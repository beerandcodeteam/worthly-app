<?php

use App\Contracts\SecureTokenStorage;
use App\Livewire\History\HistoryPage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

beforeEach(function () {
    config()->set('services.worthly.base_url', 'https://api.worthly.test');
    fakeWorthlyApi();
    app(SecureTokenStorage::class)->put('history-test-token');
    Carbon::setTestNow('2026-05-17 10:00:00');
});

afterEach(function () {
    Carbon::setTestNow();
});

function historyAnalysisRow(int $id, array $overrides = []): array
{
    return array_replace_recursive([
        'id' => $id,
        'product' => [
            'name' => "Product {$id}",
            'category' => 'Audio',
            'estimated_price_range' => '$100 - $150',
        ],
        'summary' => "Summary {$id}",
        'recommendation' => [
            'decision' => 'buy',
            'reason' => "Reason {$id}",
        ],
        'input_type' => 'text',
        'created_at' => '2026-05-17T09:00:00Z',
    ], $overrides);
}

function fakeHistoryPage(int $page, array $items, ?string $nextUrl = null): void
{
    Http::fake([
        'api.worthly.test/api/analyses?page='.$page => Http::response([
            'data' => $items,
            'meta' => ['total' => count($items), 'per_page' => 15, 'current_page' => $page],
            'links' => ['next' => $nextUrl, 'prev' => null],
        ], 200),
    ]);
}

// =====================================================================
// 7.1 — Paginated History (US-9.1)
// =====================================================================

it('fetches page 1 on load and renders every analysis row', function () {
    $items = [
        historyAnalysisRow(101, ['product' => ['name' => 'First Product']]),
        historyAnalysisRow(102, ['product' => ['name' => 'Second Product']]),
        historyAnalysisRow(103, ['product' => ['name' => 'Third Product']]),
    ];
    fakeHistoryPage(1, $items);

    $component = Livewire::test(HistoryPage::class)
        ->assertSeeText('First Product')
        ->assertSeeText('Second Product')
        ->assertSeeText('Third Product')
        ->assertSeeHtml('data-testid="history-page"')
        ->assertSeeHtml('data-testid="history-list"');

    expect($component->get('rows'))->toHaveCount(3);
    expect($component->get('currentPage'))->toBe(1);

    Http::assertSent(fn ($request) => str_starts_with($request->url(), 'https://api.worthly.test/api/analyses')
        && $request['page'] === 1);
});

it('loads the next page when the user reaches the end', function () {
    $firstPage = [
        historyAnalysisRow(201, ['product' => ['name' => 'Page1 Product']]),
    ];
    $secondPage = [
        historyAnalysisRow(202, ['product' => ['name' => 'Page2 Product']]),
    ];

    fakeHistoryPage(1, $firstPage, nextUrl: 'https://api.worthly.test/api/analyses?page=2');

    $component = Livewire::test(HistoryPage::class)
        ->assertSeeText('Page1 Product')
        ->assertSet('hasNextPage', true);

    fakeHistoryPage(2, $secondPage, nextUrl: null);

    $component->call('loadMore')
        ->assertSeeText('Page1 Product')
        ->assertSeeText('Page2 Product')
        ->assertSet('hasNextPage', false);

    expect($component->get('rows'))->toHaveCount(2);
    expect($component->get('currentPage'))->toBe(2);

    Http::assertSent(fn ($request) => str_contains($request->url(), '/api/analyses')
        && (int) ($request['page'] ?? 0) === 2);
});

it('groups rows by Today / Yesterday / This week / Earlier', function () {
    $items = [
        historyAnalysisRow(301, [
            'product' => ['name' => 'Today Product'],
            'created_at' => '2026-05-17T08:00:00Z',
        ]),
        historyAnalysisRow(302, [
            'product' => ['name' => 'Yesterday Product'],
            'created_at' => '2026-05-16T14:00:00Z',
        ]),
        historyAnalysisRow(303, [
            'product' => ['name' => 'ThisWeek Product'],
            'created_at' => '2026-05-13T14:00:00Z',
        ]),
        historyAnalysisRow(304, [
            'product' => ['name' => 'Earlier Product'],
            'created_at' => '2026-04-01T14:00:00Z',
        ]),
    ];

    fakeHistoryPage(1, $items);

    $component = Livewire::test(HistoryPage::class)
        ->assertSeeText('Today')
        ->assertSeeText('Yesterday')
        ->assertSeeText('This week')
        ->assertSeeText('Earlier')
        ->assertSeeText('Today Product')
        ->assertSeeText('Yesterday Product')
        ->assertSeeText('ThisWeek Product')
        ->assertSeeText('Earlier Product');

    $grouped = $component->instance()->groupedRows();

    expect($grouped['today'])->toHaveCount(1);
    expect($grouped['today'][0]['id'])->toBe(301);
    expect($grouped['yesterday'])->toHaveCount(1);
    expect($grouped['yesterday'][0]['id'])->toBe(302);
    expect($grouped['this_week'])->toHaveCount(1);
    expect($grouped['this_week'][0]['id'])->toBe(303);
    expect($grouped['earlier'])->toHaveCount(1);
    expect($grouped['earlier'][0]['id'])->toBe(304);
});

it('resets to page 1 on pull-to-refresh', function () {
    $firstPagePayload = [
        'data' => [historyAnalysisRow(401, ['product' => ['name' => 'Old Page1']])],
        'meta' => ['total' => 2, 'per_page' => 15, 'current_page' => 1],
        'links' => ['next' => 'https://api.worthly.test/api/analyses?page=2', 'prev' => null],
    ];
    $secondPagePayload = [
        'data' => [historyAnalysisRow(402, ['product' => ['name' => 'Old Page2']])],
        'meta' => ['total' => 2, 'per_page' => 15, 'current_page' => 2],
        'links' => ['next' => null, 'prev' => null],
    ];
    $freshPagePayload = [
        'data' => [historyAnalysisRow(403, ['product' => ['name' => 'Fresh Product']])],
        'meta' => ['total' => 1, 'per_page' => 15, 'current_page' => 1],
        'links' => ['next' => null, 'prev' => null],
    ];

    Http::fake([
        'api.worthly.test/api/analyses?page=1' => Http::sequence()
            ->push($firstPagePayload, 200)
            ->push($freshPagePayload, 200),
        'api.worthly.test/api/analyses?page=2' => Http::response($secondPagePayload, 200),
    ]);

    $component = Livewire::test(HistoryPage::class);

    $component->call('loadMore');

    expect($component->get('rows'))->toHaveCount(2);
    expect($component->get('currentPage'))->toBe(2);

    $component->call('refresh');

    expect($component->get('rows'))->toHaveCount(1);
    expect($component->get('rows')[0]['id'])->toBe(403);
    expect($component->get('currentPage'))->toBe(1);
    expect($component->get('hasNextPage'))->toBeFalse();

    $component->assertSeeText('Fresh Product')
        ->assertDontSeeText('Old Page1')
        ->assertDontSeeText('Old Page2');
});

it('triggers the global 401 handler on 401', function () {
    Cache::put('auth.user', ['name' => 'Ada']);
    Cache::put('analyses.recent', [['id' => 1]]);

    Http::fake([
        'api.worthly.test/api/analyses*' => Http::response(['message' => 'Unauthenticated.'], 401),
    ]);

    Livewire::test(HistoryPage::class)
        ->assertRedirect(route('login'));

    expect(app(SecureTokenStorage::class)->get())->toBeNull();
    expect(Cache::get('auth.user'))->toBeNull();
    expect(Cache::get('analyses.recent'))->toBeNull();
    expect(session('toast'))->toBe('Session expired. Please sign in again.');
});

// =====================================================================
// 7.2 — Filter by Verdict (US-9.2)
// =====================================================================

it('filters the loaded rows by verdict bucket', function () {
    $items = [
        historyAnalysisRow(501, [
            'product' => ['name' => 'Buy Product'],
            'recommendation' => ['decision' => 'buy', 'reason' => 'Yes'],
        ]),
        historyAnalysisRow(502, [
            'product' => ['name' => 'Wait Product'],
            'recommendation' => ['decision' => 'wait', 'reason' => 'Hmm'],
        ]),
        historyAnalysisRow(503, [
            'product' => ['name' => 'Skip Product'],
            'recommendation' => ['decision' => 'do_not_buy', 'reason' => 'No'],
        ]),
    ];

    fakeHistoryPage(1, $items);

    $component = Livewire::test(HistoryPage::class)
        ->assertSeeText('Buy Product')
        ->assertSeeText('Wait Product')
        ->assertSeeText('Skip Product');

    $component->call('setFilter', 'buy')
        ->assertSeeText('Buy Product')
        ->assertDontSeeText('Wait Product')
        ->assertDontSeeText('Skip Product');

    $component->call('setFilter', 'wait')
        ->assertSeeText('Wait Product')
        ->assertDontSeeText('Buy Product')
        ->assertDontSeeText('Skip Product');

    $component->call('setFilter', 'skip')
        ->assertSeeText('Skip Product')
        ->assertDontSeeText('Buy Product')
        ->assertDontSeeText('Wait Product');

    $component->call('setFilter', 'all')
        ->assertSeeText('Buy Product')
        ->assertSeeText('Wait Product')
        ->assertSeeText('Skip Product');
});

it('shows an empty-state message when a filter yields zero results', function () {
    $items = [
        historyAnalysisRow(601, [
            'product' => ['name' => 'Only Buy Product'],
            'recommendation' => ['decision' => 'buy', 'reason' => 'Reason'],
        ]),
    ];

    fakeHistoryPage(1, $items);

    Livewire::test(HistoryPage::class)
        ->call('setFilter', 'skip')
        ->assertSeeHtml('data-testid="history-filtered-empty"')
        ->assertSeeText('No Skip analyses yet')
        ->assertDontSeeText('Only Buy Product');
});

it('clears the filter when the user leaves the tab', function () {
    $items = [
        historyAnalysisRow(701, [
            'recommendation' => ['decision' => 'buy', 'reason' => 'Reason'],
        ]),
    ];

    fakeHistoryPage(1, $items);

    $component = Livewire::test(HistoryPage::class)
        ->call('setFilter', 'skip')
        ->assertSet('filter', 'skip');

    $component->call('clearFilter')
        ->assertSet('filter', 'all');
});
