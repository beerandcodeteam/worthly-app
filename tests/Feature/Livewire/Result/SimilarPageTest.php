<?php

use App\Contracts\SecureTokenStorage;
use App\Livewire\Result\ResultPage;
use App\Livewire\Result\SimilarPage;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;

beforeEach(function () {
    config()->set('services.worthly.base_url', 'https://api.worthly.test');
    fakeWorthlyApi();
    app(SecureTokenStorage::class)->put('similar-test-token');
});

function buildResultAnalysis(int $id, array $overrides = []): array
{
    $data = worthlyAnalysisPayload(['id' => $id])['data'];

    foreach ($overrides as $key => $value) {
        data_set($data, $key, $value);
    }

    Cache::put('analyses.'.$id, $data);

    return $data;
}

it('lists every similar product with name, reason, and price reference', function () {
    buildResultAnalysis(1100, [
        'similar_products' => [
            ['name' => 'Alt One', 'reason' => 'Cheaper option with similar features.', 'price_reference' => '$50 - $70'],
            ['name' => 'Alt Two', 'reason' => 'Premium alternative for power users.', 'price_reference' => '$120 - $150'],
        ],
    ]);

    Livewire::test(SimilarPage::class, ['analysis' => 1100])
        ->assertSeeText('Alt One')
        ->assertSeeText('Cheaper option with similar features.')
        ->assertSeeText('$50 - $70')
        ->assertSeeText('Alt Two')
        ->assertSeeText('Premium alternative for power users.')
        ->assertSeeText('$120 - $150');
});

it('falls back to em-dash when price_reference is null', function () {
    buildResultAnalysis(1101, [
        'similar_products' => [
            ['name' => 'No Price Alt', 'reason' => 'A reasonable alternative.', 'price_reference' => null],
        ],
    ]);

    Livewire::test(SimilarPage::class, ['analysis' => 1101])
        ->assertSeeText('No Price Alt')
        ->assertSeeText('—');
});

it('hides the drill-in row on the Result screen when similar_products is empty', function () {
    buildResultAnalysis(1102, ['similar_products' => []]);

    Livewire::test(ResultPage::class, ['analysis' => 1102])
        ->assertDontSeeHtml('data-testid="drillin-similar"');
});

it('caps the list at 5 items per the API contract', function () {
    $many = [];
    for ($i = 1; $i <= 8; $i++) {
        $many[] = [
            'name' => "Alternative {$i}",
            'reason' => "Reason {$i}",
            'price_reference' => '$'.($i * 10).' - $'.($i * 10 + 5),
        ];
    }

    buildResultAnalysis(1103, ['similar_products' => $many]);

    $component = Livewire::test(SimilarPage::class, ['analysis' => 1103]);

    expect(count($component->instance()->similarRows()))->toBe(5);

    $component
        ->assertSeeText('Alternative 1')
        ->assertSeeText('Alternative 5')
        ->assertDontSeeText('Alternative 6')
        ->assertDontSeeText('Alternative 7')
        ->assertDontSeeText('Alternative 8');
});

it('shows the drill-in row on the Result screen when similar_products has items', function () {
    buildResultAnalysis(1104, [
        'similar_products' => [
            ['name' => 'Alt', 'reason' => 'Cheap', 'price_reference' => '$10'],
        ],
    ]);

    Livewire::test(ResultPage::class, ['analysis' => 1104])
        ->assertSeeHtml('data-testid="drillin-similar"')
        ->assertSeeText('1 alternative');
});
