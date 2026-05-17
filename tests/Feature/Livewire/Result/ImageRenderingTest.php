<?php

use App\Contracts\SecureTokenStorage;
use App\Livewire\Result\ResultPage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

beforeEach(function () {
    config()->set('services.worthly.base_url', 'https://api.worthly.test');
    fakeWorthlyApi();
    app(SecureTokenStorage::class)->put('image-rendering-test-token');
});

function cacheImageAnalysis(int $id, array $overrides = []): array
{
    $payload = worthlyAnalysisPayload(array_merge(
        ['id' => $id, 'input_type' => 'image'],
        $overrides,
    ));

    Cache::put('analyses.'.$id, $payload['data']);

    return $payload['data'];
}

it('fetches the image with the bearer token for image analyses', function () {
    Http::fake([
        'api.worthly.test/api/analyses/1000/image' => Http::response(
            'binary-image-bytes',
            200,
            ['Content-Type' => 'image/png'],
        ),
    ]);

    cacheImageAnalysis(1000);

    Livewire::test(ResultPage::class, ['analysis' => 1000])
        ->call('loadImage')
        ->assertSeeHtml('data-testid="hero-image"')
        ->assertSeeHtml('data:image/png;base64,'.base64_encode('binary-image-bytes'));

    Http::assertSent(fn ($request) => $request->url() === 'https://api.worthly.test/api/analyses/1000/image'
        && $request->method() === 'GET'
        && $request->hasHeader('Authorization', 'Bearer image-rendering-test-token'));
});

it('never calls the image endpoint for text analyses', function () {
    Http::fake();

    $payload = worthlyAnalysisPayload(['id' => 1001, 'input_type' => 'text']);
    Cache::put('analyses.1001', $payload['data']);

    Livewire::test(ResultPage::class, ['analysis' => 1001])
        ->call('loadImage')
        ->assertDontSeeHtml('data-testid="hero-image-card"')
        ->assertDontSeeHtml('data-testid="hero-image-skeleton"');

    Http::assertNotSent(fn ($request) => str_contains($request->url(), '/image'));
});

it('shows an "Image unavailable" placeholder on 404', function () {
    Http::fake([
        'api.worthly.test/api/analyses/1002/image' => Http::response(
            ['message' => 'Image not found.'],
            404,
        ),
    ]);

    cacheImageAnalysis(1002);

    Livewire::test(ResultPage::class, ['analysis' => 1002])
        ->call('loadImage')
        ->assertSeeHtml('data-testid="hero-image-unavailable"')
        ->assertSeeText('Image unavailable')
        ->assertDontSeeHtml('data-testid="hero-image-skeleton"');
});

it('triggers the global 401 handler on 401', function () {
    Http::fake([
        'api.worthly.test/api/analyses/1003/image' => Http::response(
            ['message' => 'Unauthenticated.'],
            401,
        ),
    ]);

    cacheImageAnalysis(1003);

    $tokens = app(SecureTokenStorage::class);

    Livewire::test(ResultPage::class, ['analysis' => 1003])
        ->call('loadImage')
        ->assertRedirect(route('login'));

    expect($tokens->get())->toBeNull();
    expect(session('toast'))->toBe('Session expired. Please sign in again.');
});

it('renders a same-aspect-ratio skeleton while loading', function () {
    cacheImageAnalysis(1004);

    $html = Livewire::test(ResultPage::class, ['analysis' => 1004])->html();

    expect($html)->toContain('data-testid="hero-image-skeleton"');
    expect($html)->toContain('aspect-ratio:1 / 1');

    $skeletonStart = strpos($html, 'data-testid="hero-image-skeleton"');
    expect($skeletonStart)->toBeInt();

    $skeletonChunk = substr($html, max(0, $skeletonStart - 400), 600);
    expect($skeletonChunk)->toContain('aspect-ratio:1 / 1');
});
