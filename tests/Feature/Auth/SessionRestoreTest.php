<?php

use App\Contracts\SecureTokenStorage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

beforeEach(function () {
    config()->set('services.worthly.base_url', 'https://api.worthly.test');
    fakeWorthlyApi();
});

it('routes to home when /api/me returns 200', function () {
    app(SecureTokenStorage::class)->put('valid-token');

    Http::fake([
        'api.worthly.test/api/me' => Http::response([
            'data' => ['name' => 'Ada', 'email' => 'ada@example.com'],
        ], 200),
    ]);

    $this->get('/')->assertRedirect(route('home'));

    expect(app(SecureTokenStorage::class)->get())->toBe('valid-token');
    expect(Cache::get('auth.user'))->toMatchArray(['name' => 'Ada']);
    Http::assertSent(fn ($request) => $request->url() === 'https://api.worthly.test/api/me');
});

it('wipes the token and routes to login when /api/me returns 401', function () {
    app(SecureTokenStorage::class)->put('expired-token');
    Cache::put('auth.user', ['name' => 'Stale']);

    Http::fake([
        'api.worthly.test/api/me' => Http::response(['message' => 'Unauthenticated.'], 401),
    ]);

    $this->get('/')->assertRedirect(route('login'));

    expect(app(SecureTokenStorage::class)->get())->toBeNull();
    expect(Cache::get('auth.user'))->toBeNull();
});

it('does not call /api/me when no token is stored', function () {
    Http::fake();

    $this->get('/')->assertRedirect(route('login'));

    Http::assertNothingSent();
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
