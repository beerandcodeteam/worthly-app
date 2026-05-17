<?php

use App\Contracts\SecureTokenStorage;
use App\Livewire\Result\ResultPage;
use App\Support\Verdict;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

beforeEach(function () {
    config()->set('services.worthly.base_url', 'https://api.worthly.test');
    fakeWorthlyApi();
    app(SecureTokenStorage::class)->put('hero-test-token');
});

function cacheResultAnalysis(int $id, array $overrides = []): array
{
    $payload = worthlyAnalysisPayload(array_merge(['id' => $id], $overrides));
    Cache::put('analyses.'.$id, $payload['data']);

    return $payload['data'];
}

it('maps every API decision to the correct verdict bucket', function (string $decision, Verdict $expected) {
    cacheResultAnalysis(700, ['recommendation' => ['decision' => $decision, 'reason' => 'Reason text']]);

    $component = Livewire::test(ResultPage::class, ['analysis' => 700]);

    expect($component->instance()->verdict())->toBe($expected);
    $component->assertSeeText($expected->code());
})->with([
    'buy decision' => ['buy', Verdict::Buy],
    'buy_if_price_is_good decision' => ['buy_if_price_is_good', Verdict::Buy],
    'wait decision' => ['wait', Verdict::Wait],
    'consider_alternatives decision' => ['consider_alternatives', Verdict::Wait],
    'do_not_buy decision' => ['do_not_buy', Verdict::Skip],
]);

it('renders price-conditional secondary copy for buy_if_price_is_good', function () {
    cacheResultAnalysis(701, [
        'recommendation' => ['decision' => 'buy_if_price_is_good', 'reason' => 'Buy only when discounted'],
    ]);

    Livewire::test(ResultPage::class, ['analysis' => 701])
        ->assertSeeHtml('data-testid="hero-price-conditional"')
        ->assertSeeText('current price');
});

it('does not render the price-conditional copy for a plain buy decision', function () {
    cacheResultAnalysis(702, [
        'recommendation' => ['decision' => 'buy', 'reason' => 'Excellent product'],
    ]);

    Livewire::test(ResultPage::class, ['analysis' => 702])
        ->assertDontSeeHtml('data-testid="hero-price-conditional"');
});

it('hides product.category and estimated_price_range when null', function () {
    cacheResultAnalysis(703, [
        'product' => [
            'name' => 'Mystery Gadget',
            'category' => null,
            'estimated_price_range' => null,
        ],
    ]);

    Livewire::test(ResultPage::class, ['analysis' => 703])
        ->assertSeeText('Mystery Gadget')
        ->assertDontSeeHtml('data-testid="hero-category"')
        ->assertDontSeeHtml('data-testid="hero-price-range"');
});

it('places the hero card above all other content', function () {
    cacheResultAnalysis(704);

    $html = Livewire::test(ResultPage::class, ['analysis' => 704])->html();

    $heroPos = strpos($html, 'data-testid="result-hero"');
    $summaryPos = strpos($html, 'data-testid="result-summary"');
    $whyPos = strpos($html, 'data-testid="result-why"');
    $pricePos = strpos($html, 'data-testid="price-card"');
    $drillsPos = strpos($html, 'data-testid="result-drillins"');
    $ctasPos = strpos($html, 'data-testid="result-bottom-ctas"');

    expect($heroPos)->toBeInt();
    expect($heroPos)->toBeLessThan($summaryPos);
    expect($heroPos)->toBeLessThan($whyPos);
    expect($heroPos)->toBeLessThan($pricePos);
    expect($heroPos)->toBeLessThan($drillsPos);
    expect($heroPos)->toBeLessThan($ctasPos);
});

it('requires authentication', function () {
    app(SecureTokenStorage::class)->forget();
    Http::fake();

    Livewire::test(ResultPage::class, ['analysis' => 1])
        ->assertRedirect(route('login'));

    Http::assertNothingSent();
});
