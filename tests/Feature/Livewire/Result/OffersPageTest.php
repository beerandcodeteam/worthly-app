<?php

use App\Contracts\SecureTokenStorage;
use App\Livewire\Result\OffersPage;
use App\Livewire\Result\ResultPage;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;

beforeEach(function () {
    config()->set('services.worthly.base_url', 'https://api.worthly.test');
    fakeWorthlyApi();
    app(SecureTokenStorage::class)->put('offers-test-token');
});

function buildOffersAnalysis(int $id, array $overrides = []): array
{
    $data = worthlyAnalysisPayload(['id' => $id])['data'];

    foreach ($overrides as $key => $value) {
        data_set($data, $key, $value);
    }

    Cache::put('analyses.'.$id, $data);

    return $data;
}

it('renders estimated_price_range as the price reference', function () {
    buildOffersAnalysis(1300, [
        'product.estimated_price_range' => '$200 - $260',
        'recommendation.reason' => 'Look for discounts before pulling the trigger.',
    ]);

    Livewire::test(OffersPage::class, ['analysis' => 1300])
        ->assertSeeHtml('data-testid="offers-price-reference"')
        ->assertSeeText('Price reference')
        ->assertSeeText('$200 - $260')
        ->assertSeeText('Look for discounts before pulling the trigger.');
});

it('sorts alternatives by price_reference when present', function () {
    buildOffersAnalysis(1301, [
        'similar_products' => [
            ['name' => 'Mid Option', 'reason' => 'mid', 'price_reference' => '$120 - $150'],
            ['name' => 'Cheapest Option', 'reason' => 'cheap', 'price_reference' => '$40 - $60'],
            ['name' => 'Premium Option', 'reason' => 'high', 'price_reference' => '$200 - $260'],
            ['name' => 'No Price Option', 'reason' => 'unknown', 'price_reference' => null],
        ],
    ]);

    $component = Livewire::test(OffersPage::class, ['analysis' => 1301]);

    $sortedNames = array_column($component->instance()->alternatives(), 'name');

    expect($sortedNames)->toBe(['Cheapest Option', 'Mid Option', 'Premium Option', 'No Price Option']);
});

it('never renders retailer list, sparkline, or stock badges', function () {
    buildOffersAnalysis(1302);

    $html = Livewire::test(OffersPage::class, ['analysis' => 1302])->html();

    expect($html)->not->toContain('data-testid="offers-retailers"');
    expect($html)->not->toContain('data-testid="offers-sparkline"');
    expect($html)->not->toContain('data-testid="offers-stock-badge"');
    expect($html)->not->toContain('data-testid="offers-best-price"');

    Livewire::test(OffersPage::class, ['analysis' => 1302])
        ->assertDontSeeText('All retailers')
        ->assertDontSeeText('Price history')
        ->assertDontSeeText('In stock')
        ->assertDontSeeText('Out of stock')
        ->assertDontSeeText('Free shipping')
        ->assertDontSeeText('Best price');
});

it('hides the drill-in row when both price reference and similar_products are missing', function () {
    buildOffersAnalysis(1303, [
        'product.estimated_price_range' => null,
        'similar_products' => [],
    ]);

    Livewire::test(ResultPage::class, ['analysis' => 1303])
        ->assertDontSeeHtml('data-testid="drillin-offers"');
});

it('shows the drill-in row when only similar_products is present', function () {
    buildOffersAnalysis(1304, [
        'product.estimated_price_range' => null,
        'similar_products' => [
            ['name' => 'Alt', 'reason' => 'r', 'price_reference' => '$30'],
        ],
    ]);

    Livewire::test(ResultPage::class, ['analysis' => 1304])
        ->assertSeeHtml('data-testid="drillin-offers"');
});
