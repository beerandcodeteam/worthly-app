<?php

use App\Contracts\SecureTokenStorage;
use App\Models\User;
use App\Support\Storage\InMemorySecureTokenStorage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind different classes or traits.
|
*/

pest()->extend(TestCase::class)
 // ->use(RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function fakeWorthlyApi(array $responses = []): HttpFactory
{
    $fake = Http::fake($responses);

    app()->instance(SecureTokenStorage::class, new InMemorySecureTokenStorage);

    return $fake;
}

function actingAsWorthlyUser(?User $user = null, string $token = 'test-worthly-token'): User
{
    $user ??= User::factory()->create();

    $storage = app(SecureTokenStorage::class);

    if (! $storage instanceof InMemorySecureTokenStorage) {
        $storage = new InMemorySecureTokenStorage;
        app()->instance(SecureTokenStorage::class, $storage);
    }

    $storage->put($token);

    test()->actingAs($user);

    return $user;
}

function worthlyAnalysisPayload(array $overrides = []): array
{
    $defaults = [
        'id' => 42,
        'product' => [
            'name' => 'Logitech MX Master 3S',
            'category' => 'Wireless mouse',
            'estimated_price_range' => '$80 - $110',
        ],
        'summary' => 'The Logitech MX Master 3S is a premium wireless mouse focused on productivity, comfort, and precision.',
        'similar_products' => [
            [
                'name' => 'Logitech MX Master 2S',
                'reason' => 'Older model with lower price and similar productivity features.',
                'price_reference' => '$60 - $80',
            ],
            [
                'name' => 'Razer Pro Click',
                'reason' => 'Alternative focused on ergonomics and professional use.',
                'price_reference' => '$80 - $100',
            ],
        ],
        'cost_benefit_analysis' => 'The MX Master 3S is worth it if the user values ergonomics, silent clicks, and productivity features.',
        'recommendation' => [
            'decision' => 'buy_if_price_is_good',
            'reason' => 'Strong product, but the best decision depends on the current price compared to alternatives.',
        ],
        'input_type' => 'text',
        'image_url' => null,
        'created_at' => '2026-05-14T10:30:00Z',
    ];

    return ['data' => array_replace_recursive($defaults, $overrides)];
}
