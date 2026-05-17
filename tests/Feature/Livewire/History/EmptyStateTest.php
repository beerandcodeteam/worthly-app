<?php

use App\Contracts\SecureTokenStorage;
use App\Livewire\History\HistoryPage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

beforeEach(function () {
    config()->set('services.worthly.base_url', 'https://api.worthly.test');
    fakeWorthlyApi();
    app(SecureTokenStorage::class)->put('empty-test-token');
    Carbon::setTestNow('2026-05-17 10:00:00');
});

afterEach(function () {
    Carbon::setTestNow();
});

it('renders the empty state when /api/analyses returns no data', function () {
    Http::fake([
        'api.worthly.test/api/analyses?page=1' => Http::response([
            'data' => [],
            'meta' => ['total' => 0, 'per_page' => 15, 'current_page' => 1],
            'links' => ['next' => null, 'prev' => null],
        ], 200),
    ]);

    Livewire::test(HistoryPage::class)
        ->assertSeeHtml('data-testid="history-empty"')
        ->assertSeeText('Nothing here yet.')
        ->assertSeeText('Send a product photo or type a product name to get your first verdict.')
        ->assertSeeText('Start an analysis');
});

it('does not flicker the empty state while page 1 is loading', function () {
    Http::fake([
        'api.worthly.test/api/analyses?page=1' => Http::response([
            'data' => [],
            'meta' => ['total' => 0, 'per_page' => 15, 'current_page' => 1],
            'links' => ['next' => null, 'prev' => null],
        ], 200),
    ]);

    $component = Livewire::test(HistoryPage::class);

    // Sanity: the initial component state never exposes the empty state while loading.
    expect($component->instance()->showInitialEmptyState())->toBeTrue();

    // Reset to "loading" to assert the flicker guard explicitly.
    $component->set('loadingPage1', true);
    expect($component->instance()->showInitialEmptyState())->toBeFalse();
    $component->assertDontSeeHtml('data-testid="history-empty"');
});

it('routes the CTA to the Home composer', function () {
    Http::fake([
        'api.worthly.test/api/analyses?page=1' => Http::response([
            'data' => [],
            'meta' => ['total' => 0, 'per_page' => 15, 'current_page' => 1],
            'links' => ['next' => null, 'prev' => null],
        ], 200),
    ]);

    Livewire::test(HistoryPage::class)
        ->call('startNewAnalysis')
        ->assertRedirect(route('home'));
});
