<?php

namespace App\Services\Worthly;

use App\Contracts\SecureTokenStorage;
use App\Services\Worthly\Exceptions\NotFoundException;
use App\Services\Worthly\Exceptions\UnauthorizedException;
use App\Services\Worthly\Exceptions\UpstreamFailureException;
use App\Services\Worthly\Exceptions\ValidationException;
use App\Services\Worthly\Exceptions\WorthlyApiException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class WorthlyApiClient
{
    public function __construct(
        private readonly SecureTokenStorage $tokens,
    ) {}

    /**
     * @param  array<string, mixed>  $body
     * @return array<string, mixed>
     */
    public function post(string $path, array $body = [], bool $unwrapEnvelope = true): array
    {
        $response = $this->request()->asJson()->post($this->url($path), $body);

        return $this->handle($response, $unwrapEnvelope);
    }

    /**
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>
     */
    public function get(string $path, array $query = [], bool $unwrapEnvelope = true): array
    {
        $response = $this->request()->get($this->url($path), $query);

        return $this->handle($response, $unwrapEnvelope);
    }

    /**
     * @return array<string, mixed>
     */
    public function delete(string $path, bool $unwrapEnvelope = true): array
    {
        $response = $this->request()->delete($this->url($path));

        return $this->handle($response, $unwrapEnvelope);
    }

    public function pendingRequest(): PendingRequest
    {
        return $this->request();
    }

    private function request(): PendingRequest
    {
        $request = Http::acceptJson()
            ->timeout((int) config('services.worthly.timeout', 30))
            ->withOptions(['http_errors' => false]);

        $token = $this->tokens->get();

        if ($token !== null && $token !== '') {
            $request = $request->withToken($token);
        }

        return $request;
    }

    private function url(string $path): string
    {
        $base = rtrim((string) config('services.worthly.base_url'), '/');
        $suffix = '/'.ltrim($path, '/');

        return $base.$suffix;
    }

    /**
     * @return array<string, mixed>
     */
    private function handle(Response $response, bool $unwrapEnvelope): array
    {
        $status = $response->status();

        if ($status >= 200 && $status < 300) {
            if ($status === 204) {
                return [];
            }

            /** @var array<string, mixed> $payload */
            $payload = (array) ($response->json() ?? []);

            if ($unwrapEnvelope && array_key_exists('data', $payload)) {
                /** @var array<string, mixed> $data */
                $data = (array) $payload['data'];

                return $data;
            }

            return $payload;
        }

        /** @var array<string, mixed> $payload */
        $payload = (array) ($response->json() ?? []);
        $message = is_string($payload['message'] ?? null) ? $payload['message'] : 'Worthly API error';

        throw match ($status) {
            401 => new UnauthorizedException($message, $status, $response, $payload),
            404 => new NotFoundException($message, $status, $response, $payload),
            422 => new ValidationException(
                message: $message,
                status: $status,
                response: $response,
                payload: $payload,
                errors: is_array($payload['errors'] ?? null) ? $payload['errors'] : [],
            ),
            502 => new UpstreamFailureException($message, $status, $response, $payload),
            default => new WorthlyApiException($message, $status, $response, $payload),
        };
    }
}
