<?php

use App\Contracts\SecureTokenStorage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

beforeEach(function () {
    config()->set('services.worthly.base_url', 'https://api.worthly.test');
    fakeWorthlyApi();
});

it('always routes to onboarding on cold start', function () {
    Http::fake();

    $this->get('/')->assertRedirect(route('onboarding'));

    Http::assertNothingSent();
});

it('refreshes the cached user profile when a valid token is present and routes to onboarding', function () {
    app(SecureTokenStorage::class)->put('valid-token');

    Http::fake([
        'api.worthly.test/api/me' => Http::response([
            'data' => ['name' => 'Ada', 'email' => 'ada@example.com'],
        ], 200),
    ]);

    $this->get('/')->assertRedirect(route('onboarding'));

    expect(app(SecureTokenStorage::class)->get())->toBe('valid-token');
    expect(Cache::get('auth.user'))->toMatchArray(['name' => 'Ada']);
    Http::assertSent(fn ($request) => $request->url() === 'https://api.worthly.test/api/me');
});

it('wipes the token and still routes to onboarding when /api/me returns 401', function () {
    app(SecureTokenStorage::class)->put('expired-token');
    Cache::put('auth.user', ['name' => 'Stale']);

    Http::fake([
        'api.worthly.test/api/me' => Http::response(['message' => 'Unauthenticated.'], 401),
    ]);

    $this->get('/')->assertRedirect(route('onboarding'));

    expect(app(SecureTokenStorage::class)->get())->toBeNull();
    expect(Cache::get('auth.user'))->toBeNull();
});

it('never logs the token in plain text', function () {
    $token = 'super-secret-token-xyz';
    app(SecureTokenStorage::class)->put($token);

    Http::fake([
        'api.worthly.test/api/me' => Http::response(['data' => ['name' => 'Ada']], 200),
    ]);

    $captured = [];
    Log::listen(function ($event) use (&$captured) {
        $captured[] = $event->message.' '.json_encode($event->context);
    });

    $this->get('/');

    foreach ($captured as $line) {
        expect($line)->not->toContain($token);
    }
});
