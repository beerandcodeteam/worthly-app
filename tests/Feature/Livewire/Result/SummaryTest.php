<?php

use App\Contracts\SecureTokenStorage;
use App\Livewire\Result\ResultPage;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;

beforeEach(function () {
    config()->set('services.worthly.base_url', 'https://api.worthly.test');
    fakeWorthlyApi();
    app(SecureTokenStorage::class)->put('summary-test-token');
});

it('renders the Advisor summary when summary is present', function () {
    $payload = worthlyAnalysisPayload(['id' => 800, 'summary' => 'A premium wireless mouse focused on productivity.']);
    Cache::put('analyses.800', $payload['data']);

    Livewire::test(ResultPage::class, ['analysis' => 800])
        ->assertSeeHtml('data-testid="result-summary"')
        ->assertSeeText('Advisor summary')
        ->assertSeeText('A premium wireless mouse focused on productivity.');
});

it('hides the Advisor summary when summary is null', function () {
    $payload = worthlyAnalysisPayload(['id' => 801, 'summary' => null]);
    Cache::put('analyses.801', $payload['data']);

    Livewire::test(ResultPage::class, ['analysis' => 801])
        ->assertDontSeeHtml('data-testid="result-summary"')
        ->assertDontSeeText('Advisor summary');
});

it('hides the Why card when cost_benefit_analysis is null', function () {
    $payload = worthlyAnalysisPayload(['id' => 802, 'cost_benefit_analysis' => null]);
    Cache::put('analyses.802', $payload['data']);

    Livewire::test(ResultPage::class, ['analysis' => 802])
        ->assertDontSeeHtml('data-testid="result-why"');
});

it('renders the Why card with pros and cons when cost_benefit_analysis has signal', function () {
    $payload = worthlyAnalysisPayload([
        'id' => 803,
        'cost_benefit_analysis' => 'The build quality is excellent, but the battery life is disappointing.',
    ]);
    Cache::put('analyses.803', $payload['data']);

    Livewire::test(ResultPage::class, ['analysis' => 803])
        ->assertSeeHtml('data-testid="result-why"')
        ->assertSeeText('Reasons for')
        ->assertSeeText('Reasons against');
});

it('falls back to a single paragraph in the Why card when splitting fails', function () {
    $payload = worthlyAnalysisPayload([
        'id' => 804,
        'cost_benefit_analysis' => 'The product was released in 2023 and ships in three colors.',
    ]);
    Cache::put('analyses.804', $payload['data']);

    Livewire::test(ResultPage::class, ['analysis' => 804])
        ->assertSeeHtml('data-testid="result-why"')
        ->assertSeeText('The product was released in 2023 and ships in three colors.')
        ->assertDontSeeText('Reasons for')
        ->assertDontSeeText('Reasons against');
});
