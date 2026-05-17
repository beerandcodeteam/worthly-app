<?php

use App\Contracts\SecureTokenStorage;
use App\Livewire\Analyze\Composer;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

beforeEach(function () {
    config()->set('services.worthly.base_url', 'https://api.worthly.test');
    fakeWorthlyApi();

    app(SecureTokenStorage::class)->put('image-test-token');
});

it('accepts jpeg, png, and webp under 8 MB', function (string $name) {
    $file = UploadedFile::fake()->image($name);

    $component = Livewire::test(Composer::class)
        ->set('image', $file);

    expect($component->get('image'))->not->toBeNull();
    $component->assertHasNoErrors('image');
})->with([
    'jpeg' => ['photo.jpg'],
    'png' => ['photo.png'],
    'webp' => ['photo.webp'],
]);

it('rejects unsupported MIME types client-side', function () {
    $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

    $component = Livewire::test(Composer::class)
        ->set('image', $file);

    $component->assertHasErrors(['image']);

    expect($component->get('image'))->toBeNull();

    Http::assertNothingSent();
});

it('rejects images larger than 8 MB client-side without hitting the API', function () {
    $file = UploadedFile::fake()->create('big.jpg', 9000, 'image/jpeg');

    $component = Livewire::test(Composer::class)
        ->set('image', $file);

    $component->assertHasErrors(['image']);

    expect($component->get('image'))->toBeNull();

    Http::assertNothingSent();
});

it('sends multipart/form-data with input_type=image and the file part named image', function () {
    Http::fake([
        'api.worthly.test/api/analyses' => Http::response(worthlyAnalysisPayload([
            'id' => 77,
            'input_type' => 'image',
        ]), 201),
    ]);

    $file = UploadedFile::fake()->image('photo.jpg');

    Livewire::test(Composer::class)
        ->set('image', $file)
        ->call('submit')
        ->assertHasNoErrors()
        ->assertRedirect(route('analyses.show', ['analysis' => 77]));

    Http::assertSent(function ($request) {
        if ($request->url() !== 'https://api.worthly.test/api/analyses') {
            return false;
        }

        if ($request->method() !== 'POST') {
            return false;
        }

        if (! $request->isMultipart()) {
            return false;
        }

        $body = $request->body();

        return str_contains($body, 'name="input_type"')
            && str_contains($body, 'image')
            && str_contains($body, 'name="image"');
    });
});

it('renders the 422 errors.image message inline next to the thumbnail', function () {
    Http::fake([
        'api.worthly.test/api/analyses' => Http::response([
            'message' => 'The given data was invalid.',
            'errors' => [
                'image' => ['The image must be a file of type: jpeg, png, webp.'],
            ],
        ], 422),
    ]);

    $file = UploadedFile::fake()->image('photo.jpg');

    $component = Livewire::test(Composer::class)
        ->set('image', $file)
        ->call('submit')
        ->assertHasErrors(['image'])
        ->assertNoRedirect();

    expect($component->errors()->first('image'))->toBe('The image must be a file of type: jpeg, png, webp.');

    $html = $component->html();
    expect($html)->toContain('data-testid="composer-error-image"');
    expect($html)->toContain('The image must be a file of type: jpeg, png, webp.');
});

it('disables Ask (or omits the query) when both text and image are present', function () {
    $file = UploadedFile::fake()->image('photo.jpg');

    $component = Livewire::test(Composer::class)
        ->set('query', 'Logitech MX Master 3S')
        ->set('image', $file);

    expect($component->instance()->canSubmit())->toBeFalse();

    $html = $component->html();
    expect($html)->toContain('aria-disabled="true"');
    expect($html)->toContain('data-testid="composer-hint-both"');

    Http::assertNothingSent();

    $component->call('submit')->assertNoRedirect();

    Http::assertNothingSent();
});
