<?php

use App\Contracts\SecureTokenStorage;
use App\Livewire\Auth\Login;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

beforeEach(function () {
    config()->set('services.worthly.base_url', 'https://api.worthly.test');
    fakeWorthlyApi();
});

it('stores the token and redirects to home on 200', function () {
    Http::fake([
        'api.worthly.test/api/login' => Http::response([
            'token' => '2|valid-token',
            'token_type' => 'Bearer',
            'user' => ['name' => 'Ada', 'email' => 'ada@example.com'],
        ], 200),
    ]);

    Livewire::test(Login::class)
        ->set('email', 'ada@example.com')
        ->set('password', 'secret-pass')
        ->call('submit')
        ->assertHasNoErrors()
        ->assertSet('formError', null)
        ->assertRedirect(route('home'));

    expect(app(SecureTokenStorage::class)->get())->toBe('2|valid-token');
    expect(Cache::get('auth.user'))->toMatchArray(['name' => 'Ada']);
});

it('shows a generic message on 401 and stores no token', function () {
    Http::fake([
        'api.worthly.test/api/login' => Http::response(['message' => 'Unauthenticated.'], 401),
    ]);

    Livewire::test(Login::class)
        ->set('email', 'ada@example.com')
        ->set('password', 'wrong')
        ->call('submit')
        ->assertHasNoErrors()
        ->assertSet('formError', 'Invalid email or password.')
        ->assertNoRedirect();

    expect(app(SecureTokenStorage::class)->get())->toBeNull();
});

it('shows field-level errors on 422', function () {
    Http::fake([
        'api.worthly.test/api/login' => Http::response([
            'message' => 'The given data was invalid.',
            'errors' => [
                'email' => ['The email field is required.'],
                'password' => ['The password field is required.'],
            ],
        ], 422),
    ]);

    Livewire::test(Login::class)
        ->set('email', '')
        ->set('password', '')
        ->call('submit')
        ->assertHasErrors(['email', 'password'])
        ->assertNoRedirect();
});

it('disables the Forgot password link and SSO buttons in MVP', function () {
    Livewire::test(Login::class)
        ->assertSet('forgotPasswordEnabled', false)
        ->assertSet('ssoEnabled', false)
        ->assertSeeHtml('data-testid="forgot-password"')
        ->assertSeeHtml('data-testid="sso-apple"')
        ->assertSeeHtml('data-testid="sso-google"')
        ->assertSeeHtml('disabled');
});
