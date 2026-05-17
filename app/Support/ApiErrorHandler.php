<?php

namespace App\Support;

use App\Contracts\SecureTokenStorage;
use App\Services\Worthly\Exceptions\UpstreamFailureException;
use App\Services\Worthly\Exceptions\ValidationException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ApiErrorHandler
{
    public const SESSION_EXPIRED_TOAST = 'Session expired. Please sign in again.';

    public const OFFLINE_TOAST = 'No connection. Check your network and try again.';

    public const ANALYSIS_GONE_TOAST = 'Analysis no longer available.';

    /**
     * @var list<string>
     */
    private const CLEARED_CACHE_KEYS = [
        'auth.user',
        'analyses.recent',
        'profile.usage.total',
    ];

    public function __construct(
        private readonly SecureTokenStorage $tokens,
    ) {}

    /**
     * Single response interceptor for 401 responses: clears the token, caches,
     * and flashes the session-expired toast for the next request.
     */
    public function handleUnauthorized(): void
    {
        $this->tokens->forget();

        foreach (self::CLEARED_CACHE_KEYS as $key) {
            Cache::forget($key);
        }

        session()->flash('toast', self::SESSION_EXPIRED_TOAST);
    }

    /**
     * Parse a 422 ValidationException into inline messages for known fields and
     * a fallback toast when only unknown fields are returned.
     *
     * @param  list<string>  $knownFields
     * @return array{inline: array<string, string>, toast: ?string}
     */
    public function parseValidationErrors(ValidationException $exception, array $knownFields): array
    {
        $inline = [];
        $hasUnknown = false;

        foreach ($exception->errors() as $field => $messages) {
            if (! is_array($messages) || $messages === []) {
                continue;
            }

            $first = (string) $messages[0];

            if (in_array((string) $field, $knownFields, true)) {
                $inline[(string) $field] = $first;
            } else {
                $hasUnknown = true;
            }
        }

        $toast = null;

        if ($inline === [] && $hasUnknown) {
            $toast = $exception->getMessage();
        }

        return [
            'inline' => $inline,
            'toast' => $toast,
        ];
    }

    /**
     * Log the upstream failure with its API error_code but never surface the
     * code to the user.
     */
    public function logUpstreamFailure(UpstreamFailureException $exception): void
    {
        $code = $exception->payload()['error_code'] ?? null;

        Log::warning('Worthly API upstream failure', [
            'status' => $exception->status(),
            'error_code' => is_string($code) ? $code : null,
            'message' => $exception->getMessage(),
        ]);
    }
}
