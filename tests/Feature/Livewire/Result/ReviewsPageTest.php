<?php

use App\Contracts\SecureTokenStorage;
use App\Livewire\Result\ResultPage;
use App\Livewire\Result\ReviewsPage;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;

beforeEach(function () {
    config()->set('services.worthly.base_url', 'https://api.worthly.test');
    fakeWorthlyApi();
    app(SecureTokenStorage::class)->put('reviews-test-token');
});

function buildReviewsAnalysis(int $id, array $overrides = []): array
{
    $data = worthlyAnalysisPayload(['id' => $id])['data'];

    foreach ($overrides as $key => $value) {
        data_set($data, $key, $value);
    }

    Cache::put('analyses.'.$id, $data);

    return $data;
}

it('reuses summary and cost_benefit_analysis from the analysis', function () {
    buildReviewsAnalysis(1200, [
        'summary' => 'Reputable headphones beloved by commuters.',
        'cost_benefit_analysis' => 'The noise cancelling is excellent, but the price is high.',
    ]);

    Livewire::test(ReviewsPage::class, ['analysis' => 1200])
        ->assertSeeText('Reputation summary')
        ->assertSeeText('Reputable headphones beloved by commuters.')
        ->assertSeeText('What Worthly considered')
        ->assertSeeText('The noise cancelling is excellent, but the price is high.')
        ->assertSeeText('Top pros')
        ->assertSeeText('Top cons');
});

it('never renders aggregate rating, review count, sentiment %, or sources', function () {
    buildReviewsAnalysis(1201);

    $component = Livewire::test(ReviewsPage::class, ['analysis' => 1201]);

    $component
        ->assertDontSeeText('reviews', escape: false)
        ->assertDontSeeText('Sources')
        ->assertDontSeeText('Positive')
        ->assertDontSeeText('Negative')
        ->assertDontSeeText('Mixed')
        ->assertDontSeeText('4.5')
        ->assertDontSeeText('out of 5')
        ->assertDontSeeText('%');

    $html = $component->html();

    expect($html)->not->toContain('data-testid="reviews-rating"');
    expect($html)->not->toContain('data-testid="reviews-count"');
    expect($html)->not->toContain('data-testid="reviews-sentiment"');
    expect($html)->not->toContain('data-testid="reviews-sources"');
});

it('hides the drill-in row when both summary and cost_benefit_analysis are null', function () {
    buildReviewsAnalysis(1202, [
        'summary' => null,
        'cost_benefit_analysis' => null,
    ]);

    Livewire::test(ResultPage::class, ['analysis' => 1202])
        ->assertDontSeeHtml('data-testid="drillin-reviews"');
});

it('shows the drill-in row when at least summary is present', function () {
    buildReviewsAnalysis(1203, [
        'summary' => 'Great product.',
        'cost_benefit_analysis' => null,
    ]);

    Livewire::test(ResultPage::class, ['analysis' => 1203])
        ->assertSeeHtml('data-testid="drillin-reviews"');
});
