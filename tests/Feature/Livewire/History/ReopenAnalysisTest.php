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
    app(SecureTokenStorage::class)->put('reopen-test-token');
    Carbon::setTestNow('2026-05-17 10:00:00');
});

afterEach(function () {
    Carbon::setTestNow();
});

function reopenHistoryListPayload(array $items): void
{
    Http::fake([
        'api.worthly.test/api/analyses?page=1' => Http::response([
            'data' => $items,
            'meta' => ['total' => count($items), 'per_page' => 15, 'current_page' => 1],
            'links' => ['next' => null, 'prev' => null],
        ], 200),
    ]);
}

it('routes to Result and renders the hero from cached row data while loading', function () {
    $row = [
        'id' => 901,
        'product' => [
            'name' => 'Reopen Product',
            'category' => 'Audio',
            'estimated_price_range' => '$120 - $180',
        ],
        'summary' => 'Reopen summary',
        'recommendation' => [
            'decision' => 'buy',
            'reason' => 'Solid reason',
        ],
        'input_type' => 'text',
        'created_at' => '2026-05-17T09:00:00Z',
    ];

    reopenHistoryListPayload([$row]);

    Http::fake([
        'api.worthly.test/api/analyses/901' => Http::response(worthlyAnalysisPayload(['id' => 901]), 200),
    ]);

    $component = Livewire::test(HistoryPage::class)
        ->assertSeeText('Reopen Product');

    $component->call('openAnalysis', 901)
        ->assertRedirect(route('analyses.show', ['analysis' => 901]));

    expect(Cache::get('analyses.901'))->toBeArray()
        ->and(Cache::get('analyses.901')['id'])->toBe(901);

    Http::assertSent(fn ($request) => $request->url() === 'https://api.worthly.test/api/analyses/901'
        && $request->method() === 'GET');
});

it('removes the row and shows a toast on 404', function () {
    $rows = [
        [
            'id' => 911,
            'product' => ['name' => 'Stale Product'],
            'recommendation' => ['decision' => 'buy', 'reason' => 'Reason'],
            'input_type' => 'text',
            'created_at' => '2026-05-17T08:00:00Z',
        ],
        [
            'id' => 912,
            'product' => ['name' => 'Live Product'],
            'recommendation' => ['decision' => 'buy', 'reason' => 'Reason'],
            'input_type' => 'text',
            'created_at' => '2026-05-17T08:00:00Z',
        ],
    ];

    reopenHistoryListPayload($rows);

    Http::fake([
        'api.worthly.test/api/analyses/911' => Http::response(['message' => 'Not found'], 404),
    ]);

    $component = Livewire::test(HistoryPage::class)
        ->assertSeeText('Stale Product')
        ->assertSeeText('Live Product');

    $component->call('openAnalysis', 911)
        ->assertNoRedirect()
        ->assertSet('toast', 'Analysis no longer available.')
        ->assertSeeText('Analysis no longer available.')
        ->assertSeeHtml('data-testid="history-toast"')
        ->assertDontSeeText('Stale Product')
        ->assertSeeText('Live Product');

    expect($component->get('rows'))->toHaveCount(1);
    expect($component->get('rows')[0]['id'])->toBe(912);
});
