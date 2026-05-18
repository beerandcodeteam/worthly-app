<?php

use App\Contracts\SecureTokenStorage;
use App\Livewire\Analyze\Composer;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

beforeEach(function () {
    config()->set('services.worthly.base_url', 'https://api.worthly.test');
    fakeWorthlyApi();
});

function authenticateAnalyzeUser(string $token = 'analyze-test-token'): void
{
    app(SecureTokenStorage::class)->put($token);
}

it('submits a text analysis and polls until the analysis completes', function () {
    authenticateAnalyzeUser();

    $pendingPayload = worthlyAnalysisPayload([
        'id' => 99,
        'status' => 'pending',
        'current_step' => null,
        'product' => ['name' => null, 'category' => null, 'estimated_price_range' => null],
        'summary' => null,
        'similar_products' => [],
        'cost_benefit_analysis' => null,
        'recommendation' => ['decision' => null, 'reason' => null],
    ]);

    $completedPayload = worthlyAnalysisPayload([
        'id' => 99,
        'status' => 'completed',
        'current_step' => 'l5',
    ]);

    Http::fake([
        'api.worthly.test/api/analyses' => Http::response($pendingPayload, 202),
        'api.worthly.test/api/analyses/99' => Http::response($completedPayload, 200),
    ]);

    $component = Livewire::test(Composer::class)
        ->set('query', 'Logitech MX Master 3S')
        ->call('submit')
        ->assertHasNoErrors()
        ->assertNoRedirect()
        ->assertSet('submitting', true)
        ->assertSet('pollingAnalysisId', 99)
        ->assertSet('analysisStatus', 'pending');

    Http::assertSent(function ($request) {
        $body = $request->data();

        return $request->url() === 'https://api.worthly.test/api/analyses'
            && $request->method() === 'POST'
            && ($body['input_type'] ?? null) === 'text'
            && ($body['query'] ?? null) === 'Logitech MX Master 3S';
    });

    $component
        ->call('pollAnalysisStatus')
        ->assertRedirect(route('analyses.show', ['analysis' => 99]));

    expect(Cache::get('analyses.99'))->toBeArray()
        ->and(Cache::get('analyses.99')['id'])->toBe(99)
        ->and(Cache::get('analyses.99')['status'])->toBe('completed');
});

it('caps the input at 1000 characters', function () {
    authenticateAnalyzeUser();

    $overlong = str_repeat('a', 1200);

    $component = Livewire::test(Composer::class)
        ->set('query', $overlong);

    expect(mb_strlen($component->get('query')))->toBe(1000);
});

it('renders field-level errors on 422', function () {
    authenticateAnalyzeUser();

    Http::fake([
        'api.worthly.test/api/analyses' => Http::response([
            'message' => 'The given data was invalid.',
            'errors' => [
                'query' => ['The query field is required.'],
            ],
        ], 422),
    ]);

    Livewire::test(Composer::class)
        ->set('query', 'a tiny query')
        ->call('submit')
        ->assertHasErrors(['query'])
        ->assertNoRedirect()
        ->assertSet('submitting', false);
});

it('shows the 502 error screen on upstream failure and keeps the input', function () {
    authenticateAnalyzeUser();

    Http::fake([
        'api.worthly.test/api/analyses' => Http::response([
            'message' => 'Worthly is having trouble right now.',
            'error_code' => 'UPSTREAM_FAILURE',
        ], 502),
    ]);

    Livewire::test(Composer::class)
        ->set('query', 'Sony WH-1000XM5')
        ->call('submit')
        ->assertSet('upstreamError', true)
        ->assertSet('submitting', false)
        ->assertSet('query', 'Sony WH-1000XM5')
        ->assertSeeHtml('data-testid="upstream-error"')
        ->assertNoRedirect();
});

it('prefills the query from ?q= and renders the loader when ?autostart=1', function () {
    authenticateAnalyzeUser();

    $component = Livewire::withQueryParams(['q' => 'Sony WH-1000XM5', 'autostart' => '1'])
        ->test(Composer::class)
        ->assertSet('query', 'Sony WH-1000XM5')
        ->assertSet('autoSubmit', true)
        ->assertSet('submitting', true);

    expect($component->html())
        ->toContain('data-testid="analyzing-loader"')
        ->toContain('wire:init="runAutoSubmit"');
});

it('runAutoSubmit fires the API call once and starts polling on 202', function () {
    authenticateAnalyzeUser();

    $pendingPayload = worthlyAnalysisPayload([
        'id' => 77,
        'status' => 'pending',
        'current_step' => null,
    ]);

    $completedPayload = worthlyAnalysisPayload([
        'id' => 77,
        'status' => 'completed',
        'current_step' => 'l5',
    ]);

    Http::fake([
        'api.worthly.test/api/analyses' => Http::response($pendingPayload, 202),
        'api.worthly.test/api/analyses/77' => Http::response($completedPayload, 200),
    ]);

    $component = Livewire::withQueryParams(['q' => 'Kindle Paperwhite', 'autostart' => '1'])
        ->test(Composer::class)
        ->assertSet('autoSubmit', true)
        ->call('runAutoSubmit')
        ->assertSet('autoSubmit', false)
        ->assertSet('pollingAnalysisId', 77)
        ->assertSet('analysisStatus', 'pending')
        ->assertNoRedirect();

    Http::assertSent(fn ($request) => $request->url() === 'https://api.worthly.test/api/analyses'
        && ($request->data()['query'] ?? null) === 'Kindle Paperwhite');

    $component
        ->call('pollAnalysisStatus')
        ->assertRedirect(route('analyses.show', ['analysis' => 77]));
});

it('runAutoSubmit is a no-op when the autoSubmit flag is false', function () {
    authenticateAnalyzeUser();

    Http::fake();

    Livewire::test(Composer::class)
        ->set('query', 'Apple AirPods Pro 2')
        ->call('runAutoSubmit')
        ->assertNoRedirect()
        ->assertSet('submitting', false);

    Http::assertNothingSent();
});

it('triggers the global 401 handler on 401', function () {
    authenticateAnalyzeUser();

    Cache::put('auth.user', ['name' => 'Ada']);
    Cache::put('analyses.recent', [['id' => 1]]);

    Http::fake([
        'api.worthly.test/api/analyses' => Http::response(['message' => 'Unauthenticated.'], 401),
    ]);

    Livewire::test(Composer::class)
        ->set('query', 'Apple AirPods Pro 2')
        ->call('submit')
        ->assertRedirect(route('login'));

    expect(app(SecureTokenStorage::class)->get())->toBeNull();
    expect(Cache::get('auth.user'))->toBeNull();
    expect(Cache::get('analyses.recent'))->toBeNull();
    expect(session('toast'))->toBe('Session expired. Please sign in again.');
});
