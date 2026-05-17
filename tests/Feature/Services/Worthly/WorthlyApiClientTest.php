<?php

use App\Contracts\SecureTokenStorage;
use App\Services\Worthly\Exceptions\NotFoundException;
use App\Services\Worthly\Exceptions\UnauthorizedException;
use App\Services\Worthly\Exceptions\UpstreamFailureException;
use App\Services\Worthly\Exceptions\ValidationException;
use App\Services\Worthly\WorthlyApiClient;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config()->set('services.worthly.base_url', 'https://api.worthly.test');
    config()->set('services.worthly.timeout', 30);
    fakeWorthlyApi();
});

it('injects bearer token on every protected call', function () {
    app(SecureTokenStorage::class)->put('plain-test-token');

    Http::fake([
        'api.worthly.test/api/me' => Http::response(['data' => ['name' => 'Ada']], 200),
        'api.worthly.test/api/analyses' => Http::response(['data' => ['id' => 1]], 201),
    ]);

    $client = app(WorthlyApiClient::class);
    $client->get('/api/me');
    $client->post('/api/analyses', ['input_type' => 'text', 'query' => 'Logitech MX Master 3S']);

    Http::assertSentCount(2);
    Http::assertSent(fn ($request) => $request->hasHeader('Authorization', 'Bearer plain-test-token'));
});

it('omits Authorization header when no token is stored', function () {
    Http::fake([
        'api.worthly.test/api/login' => Http::response(['data' => ['token' => 'x']], 200),
    ]);

    app(WorthlyApiClient::class)->post('/api/login', ['email' => 'hi@example.com', 'password' => 'secret']);

    Http::assertSent(fn ($request) => ! $request->hasHeader('Authorization'));
});

it('parses {data: …} envelopes for resource endpoints', function () {
    Http::fake([
        'api.worthly.test/api/me' => Http::response([
            'data' => ['name' => 'Ada', 'email' => 'ada@example.com'],
        ], 200),
    ]);

    $result = app(WorthlyApiClient::class)->get('/api/me');

    expect($result)->toBe(['name' => 'Ada', 'email' => 'ada@example.com']);
});

it('surfaces 401 / 404 / 422 / 502 as typed exceptions', function () {
    Http::fake([
        'api.worthly.test/api/unauthorized' => Http::response(['message' => 'Unauthenticated.'], 401),
        'api.worthly.test/api/missing' => Http::response(['message' => 'Not found.'], 404),
        'api.worthly.test/api/invalid' => Http::response([
            'message' => 'The given data was invalid.',
            'errors' => ['email' => ['The email has already been taken.']],
        ], 422),
        'api.worthly.test/api/upstream' => Http::response(['message' => 'Upstream failure.', 'error_code' => 'llm_failed'], 502),
    ]);

    $client = app(WorthlyApiClient::class);

    expect(fn () => $client->get('/api/unauthorized'))->toThrow(UnauthorizedException::class);
    expect(fn () => $client->get('/api/missing'))->toThrow(NotFoundException::class);

    try {
        $client->post('/api/invalid', []);
        $this->fail('Expected ValidationException');
    } catch (ValidationException $e) {
        expect($e->status())->toBe(422)
            ->and($e->errors())->toHaveKey('email')
            ->and($e->errors()['email'][0])->toBe('The email has already been taken.');
    }

    expect(fn () => $client->post('/api/upstream', []))->toThrow(UpstreamFailureException::class);
});
