<?php

use App\Contracts\SecureTokenStorage;
use App\Livewire\Auth\Register;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

beforeEach(function () {
    config()->set('services.worthly.base_url', 'https://api.worthly.test');
    fakeWorthlyApi();
});

it('submits valid payload, stores the token, and redirects to home', function () {
    Http::fake([
        'api.worthly.test/api/register' => Http::response([
            'token' => '1|plain-text-sanctum-token',
            'token_type' => 'Bearer',
            'user' => ['name' => 'Ada Lovelace', 'email' => 'ada@example.com'],
        ], 201),
    ]);

    Livewire::test(Register::class)
        ->set('name', 'Ada Lovelace')
        ->set('email', 'ada@example.com')
        ->set('password', 'analytical-engine')
        ->set('password_confirmation', 'analytical-engine')
        ->call('submit')
        ->assertHasNoErrors()
        ->assertRedirect(route('home'));

    expect(app(SecureTokenStorage::class)->get())->toBe('1|plain-text-sanctum-token');
    expect(Cache::get('auth.user'))->toMatchArray(['name' => 'Ada Lovelace', 'email' => 'ada@example.com']);
});

it('renders field-level errors for 422', function () {
    Http::fake([
        'api.worthly.test/api/register' => Http::response([
            'message' => 'The given data was invalid.',
            'errors' => [
                'email' => ['The email has already been taken.'],
                'password' => ['The password must be at least 8 characters.'],
            ],
        ], 422),
    ]);

    Livewire::test(Register::class)
        ->set('name', 'Ada')
        ->set('email', 'taken@example.com')
        ->set('password', 'short')
        ->set('password_confirmation', 'short')
        ->call('submit')
        ->assertHasErrors(['email', 'password'])
        ->assertNoRedirect();
});

it('never stores a token on validation failure', function () {
    Http::fake([
        'api.worthly.test/api/register' => Http::response([
            'message' => 'The given data was invalid.',
            'errors' => ['email' => ['The email has already been taken.']],
        ], 422),
    ]);

    Livewire::test(Register::class)
        ->set('name', 'Ada')
        ->set('email', 'taken@example.com')
        ->set('password', 'password123')
        ->set('password_confirmation', 'password123')
        ->call('submit');

    expect(app(SecureTokenStorage::class)->get())->toBeNull();
    expect(Cache::get('auth.user'))->toBeNull();
});

it('forwards name, email, password, and password_confirmation in the request body', function () {
    Http::fake([
        'api.worthly.test/api/register' => Http::response([
            'token' => 'tok',
            'user' => ['name' => 'Ada', 'email' => 'ada@example.com'],
        ], 201),
    ]);

    Livewire::test(Register::class)
        ->set('name', 'Ada')
        ->set('email', 'ada@example.com')
        ->set('password', 'difference-engine')
        ->set('password_confirmation', 'difference-engine')
        ->call('submit');

    Http::assertSent(function ($request) {
        return $request->url() === 'https://api.worthly.test/api/register'
            && $request['name'] === 'Ada'
            && $request['email'] === 'ada@example.com'
            && $request['password'] === 'difference-engine'
            && $request['password_confirmation'] === 'difference-engine';
    });
});
