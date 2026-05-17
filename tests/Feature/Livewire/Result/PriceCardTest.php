<?php

use App\Contracts\SecureTokenStorage;
use App\Livewire\Result\ResultPage;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;

beforeEach(function () {
    config()->set('services.worthly.base_url', 'https://api.worthly.test');
    fakeWorthlyApi();
    app(SecureTokenStorage::class)->put('price-card-test-token');
});

it('renders the estimated price range as the headline', function () {
    $payload = worthlyAnalysisPayload([
        'id' => 900,
        'product' => [
            'name' => 'Logitech MX Master 3S',
            'category' => 'Wireless mouse',
            'estimated_price_range' => '$80 - $110',
        ],
    ]);
    Cache::put('analyses.900', $payload['data']);

    Livewire::test(ResultPage::class, ['analysis' => 900])
        ->assertSeeHtml('data-testid="price-card"')
        ->assertSeeHtml('data-testid="price-headline"')
        ->assertSeeText('$80 - $110')
        ->assertSeeText('Estimated by Worthly');
});

it('hides the entire card when estimated_price_range is null', function () {
    $payload = worthlyAnalysisPayload([
        'id' => 901,
        'product' => [
            'name' => 'Mystery Gadget',
            'category' => 'Audio',
            'estimated_price_range' => null,
        ],
    ]);
    Cache::put('analyses.901', $payload['data']);

    Livewire::test(ResultPage::class, ['analysis' => 901])
        ->assertDontSeeHtml('data-testid="price-card"')
        ->assertDontSeeText('Price right now');
});

it('does not render a live-price marker (post-MVP)', function () {
    $payload = worthlyAnalysisPayload(['id' => 902]);
    Cache::put('analyses.902', $payload['data']);

    $html = Livewire::test(ResultPage::class, ['analysis' => 902])->html();

    expect($html)->toContain('data-testid="price-band"');
    expect($html)->not->toContain('data-testid="price-current-marker"');
    expect($html)->not->toContain('data-testid="price-fair-marker"');
    expect($html)->not->toContain('data-testid="price-live"');
});
