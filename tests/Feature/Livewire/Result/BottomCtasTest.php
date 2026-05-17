<?php

use App\Contracts\SecureTokenStorage;
use App\Livewire\Home\HomePage;
use App\Livewire\Result\ResultPage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

beforeEach(function () {
    config()->set('services.worthly.base_url', 'https://api.worthly.test');
    fakeWorthlyApi();
    app(SecureTokenStorage::class)->put('ctas-test-token');
});

it('routes New analysis to Home with a cleared composer', function () {
    $payload = worthlyAnalysisPayload(['id' => 1000]);
    Cache::put('analyses.1000', $payload['data']);

    Livewire::test(ResultPage::class, ['analysis' => 1000])
        ->call('newAnalysis')
        ->assertRedirect(route('home'));

    Http::fake([
        'api.worthly.test/api/analyses?page=1' => Http::response([
            'data' => [],
            'meta' => ['total' => 0, 'per_page' => 15, 'current_page' => 1],
            'links' => ['next' => null, 'prev' => null],
        ], 200),
    ]);

    Livewire::test(HomePage::class)->assertSet('composer', '');
});

it('routes See best offer to the Offers drill-in even with no offers data', function () {
    $payload = worthlyAnalysisPayload([
        'id' => 1001,
        'product' => [
            'name' => 'Niche Item',
            'category' => null,
            'estimated_price_range' => null,
        ],
        'similar_products' => [],
    ]);
    Cache::put('analyses.1001', $payload['data']);

    Livewire::test(ResultPage::class, ['analysis' => 1001])
        ->call('seeBestOffer')
        ->assertRedirect(route('analyses.offers', ['analysis' => 1001]));
});

it('renders both CTAs in the bottom bar', function () {
    $payload = worthlyAnalysisPayload(['id' => 1002]);
    Cache::put('analyses.1002', $payload['data']);

    Livewire::test(ResultPage::class, ['analysis' => 1002])
        ->assertSeeHtml('data-testid="cta-new-analysis"')
        ->assertSeeHtml('data-testid="cta-see-best-offer"')
        ->assertSeeText('New analysis')
        ->assertSeeText('See best offer');
});
