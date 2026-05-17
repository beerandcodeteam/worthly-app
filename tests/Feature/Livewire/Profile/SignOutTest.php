<?php

use App\Contracts\SecureTokenStorage;
use App\Livewire\Profile\SignOutAction;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

beforeEach(function () {
    config()->set('services.worthly.base_url', 'https://api.worthly.test');
    fakeWorthlyApi();
});

it('shows a confirmation prompt before signing out', function () {
    Http::fake();

    Livewire::test(SignOutAction::class)
        ->assertSet('confirming', false)
        ->assertSeeHtml('data-testid="sign-out-trigger"')
        ->assertDontSeeHtml('data-testid="sign-out-confirm"')
        ->call('confirm')
        ->assertSet('confirming', true)
        ->assertSeeHtml('data-testid="sign-out-confirm"')
        ->assertSeeText('Sign out of Worthly?');

    Http::assertNothingSent();
});

it('clears the local token on 204', function () {
    app(SecureTokenStorage::class)->put('current-token');
    Cache::put('auth.user', ['name' => 'Ada']);

    Http::fake([
        'api.worthly.test/api/logout' => Http::response(null, 204),
    ]);

    Livewire::test(SignOutAction::class)
        ->call('confirm')
        ->call('signOut')
        ->assertRedirect(route('login'));

    expect(app(SecureTokenStorage::class)->get())->toBeNull();
    Http::assertSent(fn ($request) => $request->url() === 'https://api.worthly.test/api/logout'
        && $request->method() === 'POST');
});

it('still clears the local token on 401', function () {
    app(SecureTokenStorage::class)->put('current-token');

    Http::fake([
        'api.worthly.test/api/logout' => Http::response(['message' => 'Unauthenticated.'], 401),
    ]);

    Livewire::test(SignOutAction::class)
        ->call('confirm')
        ->call('signOut')
        ->assertRedirect(route('login'));

    expect(app(SecureTokenStorage::class)->get())->toBeNull();
});

it('clears cached recent analyses and profile on sign out', function () {
    app(SecureTokenStorage::class)->put('current-token');
    Cache::put('auth.user', ['name' => 'Ada', 'email' => 'ada@example.com']);
    Cache::put('analyses.recent', [['id' => 1], ['id' => 2]]);

    Http::fake([
        'api.worthly.test/api/logout' => Http::response(null, 204),
    ]);

    Livewire::test(SignOutAction::class)
        ->call('confirm')
        ->call('signOut');

    expect(Cache::get('auth.user'))->toBeNull();
    expect(Cache::get('analyses.recent'))->toBeNull();
});
