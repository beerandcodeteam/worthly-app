<?php

namespace App\Services\Worthly;

use App\Services\Worthly\Exceptions\NotFoundException;
use App\Services\Worthly\Exceptions\UnauthorizedException;
use App\Services\Worthly\Exceptions\UpstreamFailureException;
use App\Services\Worthly\Exceptions\ValidationException;
use App\Services\Worthly\Exceptions\WorthlyApiException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Log;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class AnalysisSubmitter
{
    public function __construct(
        private readonly WorthlyApiClient $api,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function submitText(string $query): array
    {
        return $this->api->post('/api/analyses', [
            'input_type' => 'text',
            'query' => $query,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function submitImage(TemporaryUploadedFile $image): array
    {
        $contents = $image->get();
        $filename = $this->safeFilename($image->getClientOriginalName(), $image->getClientOriginalExtension());
        $mimeType = $this->resolveMimeType($image);

        $response = $this->api->pendingRequest()
            ->attach('image', $contents, $filename, ['Content-Type' => $mimeType])
            ->attach('input_type', 'image')
            ->post($this->resolveUrl('/api/analyses'));

        return $this->unwrapResponse($response);
    }

    private function resolveMimeType(TemporaryUploadedFile $image): string
    {
        $candidates = [
            method_exists($image, 'getMimeType') ? $image->getMimeType() : null,
            $image->getClientMimeType(),
        ];

        foreach ($candidates as $candidate) {
            if (is_string($candidate) && $candidate !== '' && $candidate !== 'application/octet-stream') {
                return $candidate;
            }
        }

        return match (strtolower((string) $image->getClientOriginalExtension())) {
            'png' => 'image/png',
            'webp' => 'image/webp',
            default => 'image/jpeg',
        };
    }

    private function safeFilename(string $original, string $extension): string
    {
        $name = trim($original);

        if ($name === '') {
            $name = 'upload.'.($extension !== '' ? $extension : 'jpg');
        }

        return $name;
    }

    private function resolveUrl(string $path): string
    {
        $base = rtrim((string) config('services.worthly.base_url'), '/');

        return $base.'/'.ltrim($path, '/');
    }

    /**
     * @return array<string, mixed>
     */
    private function unwrapResponse(Response $response): array
    {
        $status = $response->status();

        if ($status >= 200 && $status < 300) {
            /** @var array<string, mixed> $payload */
            $payload = (array) ($response->json() ?? []);

            if (array_key_exists('data', $payload) && is_array($payload['data'])) {
                /** @var array<string, mixed> $data */
                $data = $payload['data'];

                return $data;
            }

            return $payload;
        }

        /** @var array<string, mixed> $payload */
        $payload = (array) ($response->json() ?? []);
        $message = is_string($payload['message'] ?? null) ? $payload['message'] : 'Worthly API error';

        Log::warning('worthly.analysis.submit.failed', [
            'status' => $status,
            'message' => $message,
            'errors' => $payload['errors'] ?? null,
            'body_preview' => mb_substr((string) $response->body(), 0, 500),
        ]);

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
