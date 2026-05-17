<?php

use App\Contracts\SecureTokenStorage;
use App\Livewire\Analyze\Composer;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

beforeEach(function () {
    config()->set('services.worthly.base_url', 'https://api.worthly.test');
    fakeWorthlyApi();

    app(SecureTokenStorage::class)->put('loader-test-token');
});

it('replaces the composer with the loader while the request is in flight', function () {
    $component = Livewire::test(Composer::class)
        ->set('query', 'Logitech MX Master 3S')
        ->set('submitting', true);

    $html = $component->html();

    expect($html)
        ->toContain('data-testid="analyzing-loader"')
        ->not->toContain('data-testid="composer-input"')
        ->not->toContain('data-testid="composer-ask"');
});

it('cycles through the five labeled steps', function () {
    $component = Livewire::test(Composer::class)
        ->set('query', 'Sony WH-1000XM5')
        ->set('submitting', true);

    $html = $component->html();

    expect($html)
        ->toContain('Identifying product')
        ->toContain('Searching the web')
        ->toContain('Reading reviews')
        ->toContain('Comparing alternatives')
        ->toContain('Forming a verdict');

    $stepCount = substr_count($html, 'data-testid="loader-step"');

    expect($stepCount)->toBe(5);
});

it('echoes the text query or the image thumbnail', function () {
    $textComponent = Livewire::test(Composer::class)
        ->set('query', 'Apple AirPods Pro 2')
        ->set('submitting', true);

    $textHtml = $textComponent->html();

    expect($textHtml)
        ->toContain('data-testid="loader-text-echo"')
        ->toContain('Apple AirPods Pro 2');

    $imageComponent = Livewire::test(Composer::class)
        ->set('image', UploadedFile::fake()->image('photo.jpg'))
        ->set('submitting', true);

    $imageHtml = $imageComponent->html();

    expect($imageHtml)
        ->toContain('data-testid="loader-image-echo"')
        ->toContain('data-testid="loader-image-thumb"');
});

it('transitions to the Result screen as soon as the API resolves', function () {
    Http::fake([
        'api.worthly.test/api/analyses' => Http::response(worthlyAnalysisPayload(['id' => 123]), 201),
    ]);

    Livewire::test(Composer::class)
        ->set('query', 'Kindle Paperwhite')
        ->call('submit')
        ->assertHasNoErrors()
        ->assertSet('submitting', false)
        ->assertRedirect(route('analyses.show', ['analysis' => 123]));
});

it('does not expose a cancel action', function () {
    $component = Livewire::test(Composer::class)
        ->set('query', 'Logitech MX Master 3S')
        ->set('submitting', true);

    $html = $component->html();

    expect($html)
        ->toContain('data-testid="analyzing-loader"')
        ->not->toContain('data-testid="loader-cancel"')
        ->not->toContain('>Cancel<')
        ->not->toContain('wire:click="cancel"');
});
