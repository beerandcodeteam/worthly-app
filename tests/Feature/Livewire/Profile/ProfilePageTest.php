<?php

use App\Contracts\SecureTokenStorage;
use App\Livewire\Profile\ProfilePage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

beforeEach(function () {
    config()->set('services.worthly.base_url', 'https://api.worthly.test');
    fakeWorthlyApi();
    app(SecureTokenStorage::class)->put('profile-test-token');
});

function fakeProfileEndpoints(array $userOverrides = [], int $total = 0): void
{
    $user = array_replace([
        'name' => 'Ada Lovelace',
        'email' => 'ada@example.com',
    ], $userOverrides);

    Http::fake([
        'api.worthly.test/api/me' => Http::response([
            'data' => $user,
        ], 200),
        'api.worthly.test/api/analyses?page=1' => Http::response([
            'data' => [],
            'meta' => ['total' => $total, 'per_page' => 15, 'current_page' => 1],
            'links' => ['next' => null, 'prev' => null],
        ], 200),
    ]);
}

it('renders name, email, and an avatar initial', function () {
    fakeProfileEndpoints(['name' => 'Ada Lovelace', 'email' => 'ada@example.com']);

    Livewire::test(ProfilePage::class)
        ->assertSet('name', 'Ada Lovelace')
        ->assertSet('email', 'ada@example.com')
        ->assertSeeHtml('data-testid="profile-name"')
        ->assertSeeHtml('data-testid="profile-email"')
        ->assertSeeHtml('data-testid="profile-avatar"')
        ->assertSeeText('Ada Lovelace')
        ->assertSeeText('ada@example.com')
        ->assertSeeText('A');

    Http::assertSent(fn ($request) => $request->url() === 'https://api.worthly.test/api/me'
        && $request->method() === 'GET');
});

it('re-fetches /api/me on pull-to-refresh', function () {
    Http::fake([
        'api.worthly.test/api/me' => Http::sequence()
            ->push(['data' => ['name' => 'Ada Lovelace', 'email' => 'ada@example.com']], 200)
            ->push(['data' => ['name' => 'Grace Hopper', 'email' => 'grace@example.com']], 200),
        'api.worthly.test/api/analyses?page=1' => Http::sequence()
            ->push([
                'data' => [],
                'meta' => ['total' => 1, 'per_page' => 15, 'current_page' => 1],
                'links' => ['next' => null, 'prev' => null],
            ], 200)
            ->push([
                'data' => [],
                'meta' => ['total' => 4, 'per_page' => 15, 'current_page' => 1],
                'links' => ['next' => null, 'prev' => null],
            ], 200),
    ]);

    $component = Livewire::test(ProfilePage::class)
        ->assertSet('name', 'Ada Lovelace')
        ->assertSet('totalAnalyses', 1);

    $component
        ->call('refresh')
        ->assertSet('name', 'Grace Hopper')
        ->assertSet('email', 'grace@example.com')
        ->assertSet('totalAnalyses', 4)
        ->assertSeeText('Grace Hopper');

    $meRequests = collect(Http::recorded())->filter(
        fn ($pair) => $pair[0]->url() === 'https://api.worthly.test/api/me'
    );

    expect($meRequests)->toHaveCount(2);
});

it('triggers the global 401 handler on 401', function () {
    Cache::put('auth.user', ['name' => 'Stale', 'email' => 'stale@example.com']);
    Cache::put('analyses.recent', [['id' => 1]]);
    Cache::put('profile.usage.total', 12);

    Http::fake([
        'api.worthly.test/api/me' => Http::response(['message' => 'Unauthenticated.'], 401),
    ]);

    Cache::forget('auth.user');

    Livewire::test(ProfilePage::class)
        ->assertRedirect(route('login'));

    expect(app(SecureTokenStorage::class)->get())->toBeNull();
    expect(Cache::get('auth.user'))->toBeNull();
    expect(Cache::get('analyses.recent'))->toBeNull();
    expect(Cache::get('profile.usage.total'))->toBeNull();
    expect(session('toast'))->toBe('Session expired. Please sign in again.');
});

it('caches the response for the session', function () {
    fakeProfileEndpoints(['name' => 'Ada Lovelace', 'email' => 'ada@example.com'], total: 7);

    Livewire::test(ProfilePage::class)
        ->assertSet('name', 'Ada Lovelace')
        ->assertSet('totalAnalyses', 7);

    expect(Cache::get('auth.user'))->toMatchArray([
        'name' => 'Ada Lovelace',
        'email' => 'ada@example.com',
    ]);
    expect(Cache::get('profile.usage.total'))->toBe(7);

    Http::assertSentCount(2);

    Livewire::test(ProfilePage::class)
        ->assertSet('name', 'Ada Lovelace')
        ->assertSet('email', 'ada@example.com')
        ->assertSet('totalAnalyses', 7);

    Http::assertSentCount(2);
});
