<?php

use App\Contracts\SecureTokenStorage;
use App\Livewire\Home\HomePage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

beforeEach(function () {
    config()->set('services.worthly.base_url', 'https://api.worthly.test');
    fakeWorthlyApi();
});

function authenticateWorthly(string $token = 'home-test-token'): void
{
    app(SecureTokenStorage::class)->put($token);
}

function fakeRecentAnalysesResponse(int $count = 3, ?int $total = null): void
{
    $items = [];
    for ($i = 1; $i <= $count; $i++) {
        $items[] = [
            'id' => $i,
            'product' => [
                'name' => "Recent Product {$i}",
                'category' => 'Audio',
                'estimated_price_range' => '$100 - $150',
            ],
            'summary' => "Short summary {$i}",
            'recommendation' => [
                'decision' => 'buy',
                'reason' => 'Good reason '.$i,
            ],
            'input_type' => 'text',
            'created_at' => '2026-05-14T10:30:00Z',
        ];
    }

    Http::fake([
        'api.worthly.test/api/analyses?page=1' => Http::response([
            'data' => $items,
            'meta' => ['total' => $total ?? $count, 'per_page' => 15, 'current_page' => 1],
            'links' => ['next' => null, 'prev' => null],
        ], 200),
    ]);
}

// =====================================================================
// 3.1 — Home Shell (US-2.1)
// =====================================================================

it('greets the user with the cached first name', function () {
    authenticateWorthly();
    Cache::put('auth.user', ['name' => 'Ada Lovelace', 'email' => 'ada@example.com']);

    fakeRecentAnalysesResponse(0);

    Livewire::test(HomePage::class)
        ->assertSet('firstName', 'Ada')
        ->assertSeeHtml('data-testid="home-greeting"')
        ->assertSeeText('Hi, Ada');
});

it('lists up to 3 recent analyses from /api/analyses page 1', function () {
    authenticateWorthly();
    Cache::put('auth.user', ['name' => 'Ada', 'email' => 'ada@example.com']);

    fakeRecentAnalysesResponse(5, total: 5);

    $component = Livewire::test(HomePage::class)
        ->assertSeeHtml('data-testid="recent-analyses"')
        ->assertSeeText('Recent Product 1')
        ->assertSeeText('Recent Product 2')
        ->assertSeeText('Recent Product 3')
        ->assertDontSeeText('Recent Product 4')
        ->assertDontSeeText('Recent Product 5');

    expect($component->get('recentAnalyses'))->toHaveCount(3);

    Http::assertSent(fn ($request) => str_starts_with($request->url(), 'https://api.worthly.test/api/analyses')
        && $request['page'] === 1);
});

it('renders 3 to 5 suggestion chips', function () {
    authenticateWorthly();
    fakeRecentAnalysesResponse(0);

    $component = Livewire::test(HomePage::class);

    $rendered = $component->html();
    $count = substr_count($rendered, 'data-testid="suggestion-chip"');

    expect($count)->toBeGreaterThanOrEqual(3)->toBeLessThanOrEqual(5);
});

it('renders the FREE plan usage indicator', function () {
    authenticateWorthly();
    fakeRecentAnalysesResponse(2, total: 32);

    Livewire::test(HomePage::class)
        ->assertSeeHtml('data-testid="plan-usage"')
        ->assertSeeHtml('data-testid="plan-badge"')
        ->assertSeeText('FREE')
        ->assertSeeText('32 / 50');
});

it('requires authentication', function () {
    app(SecureTokenStorage::class)->forget();
    Http::fake();

    Livewire::test(HomePage::class)
        ->assertRedirect(route('login'));

    Http::assertNothingSent();
});

// =====================================================================
// 3.2 — Reopen Recent Analysis (US-2.2)
// =====================================================================

it('shows a loading state then routes to Result on 200', function () {
    authenticateWorthly();
    fakeRecentAnalysesResponse(1);

    Http::fake([
        'api.worthly.test/api/analyses/1' => Http::response(worthlyAnalysisPayload(['id' => 1]), 200),
    ]);

    $component = Livewire::test(HomePage::class)
        ->assertSet('openingAnalysisId', null);

    // Recent card markup is present and exposes the loading affordance template.
    expect($component->html())->toContain('data-testid="recent-card"');

    $component
        ->call('openAnalysis', 1)
        ->assertRedirect(route('analyses.show', ['analysis' => 1]));

    expect(Cache::get('analyses.1'))->toBeArray()
        ->and(Cache::get('analyses.1')['id'])->toBe(1);

    Http::assertSent(fn ($request) => $request->url() === 'https://api.worthly.test/api/analyses/1'
        && $request->method() === 'GET');
});

it('renders an "Analysis no longer available" toast on 404 and stays on Home', function () {
    authenticateWorthly();
    fakeRecentAnalysesResponse(1);

    Http::fake([
        'api.worthly.test/api/analyses/1' => Http::response(['message' => 'Not found'], 404),
    ]);

    Livewire::test(HomePage::class)
        ->call('openAnalysis', 1)
        ->assertNoRedirect()
        ->assertSet('toast', 'Analysis no longer available.')
        ->assertSet('openingAnalysisId', null)
        ->assertSeeText('Analysis no longer available.')
        ->assertSeeHtml('data-testid="home-toast"');
});

it('triggers the global 401 handler on 401', function () {
    authenticateWorthly();
    fakeRecentAnalysesResponse(1);

    Cache::put('auth.user', ['name' => 'Ada']);
    Cache::put('analyses.recent', [['id' => 1]]);

    Http::fake([
        'api.worthly.test/api/analyses/1' => Http::response(['message' => 'Unauthenticated.'], 401),
    ]);

    Livewire::test(HomePage::class)
        ->call('openAnalysis', 1)
        ->assertRedirect(route('login'));

    expect(app(SecureTokenStorage::class)->get())->toBeNull();
    expect(Cache::get('auth.user'))->toBeNull();
    expect(Cache::get('analyses.recent'))->toBeNull();
    expect(session('toast'))->toBe('Session expired. Please sign in again.');
});

// =====================================================================
// 3.3 — Suggestion Chips (US-2.3)
// =====================================================================

it('prefills the composer text input when a chip is tapped', function () {
    authenticateWorthly();
    fakeRecentAnalysesResponse(0);

    Livewire::test(HomePage::class)
        ->assertSet('composer', '')
        ->call('prefillSuggestion', 'Logitech MX Master 3S')
        ->assertSet('composer', 'Logitech MX Master 3S')
        ->assertSeeHtml('Logitech MX Master 3S');
});

it('enables the Ask CTA once the composer has content', function () {
    authenticateWorthly();
    fakeRecentAnalysesResponse(0);

    $component = Livewire::test(HomePage::class);

    expect($component->html())->toContain('data-testid="composer-ask"');
    expect($component->html())->toContain('aria-disabled="true"');

    $component->set('composer', 'Sony WH-1000XM5');

    expect($component->html())->toContain('aria-disabled="false"');
});

it('does not auto-submit the suggestion', function () {
    authenticateWorthly();
    fakeRecentAnalysesResponse(0);

    $component = Livewire::test(HomePage::class);

    Http::assertSentCount(1); // baseline: just the mount() recent-analyses fetch.

    $component
        ->call('prefillSuggestion', 'Apple AirPods Pro 2')
        ->assertSet('composer', 'Apple AirPods Pro 2')
        ->assertNoRedirect();

    // Prefill must NOT trigger any submission to /api/analyses.
    Http::assertSentCount(1);
});
