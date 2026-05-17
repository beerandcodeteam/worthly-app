<?php

namespace App\Services\Worthly\Exceptions;

use Illuminate\Http\Client\Response;
use Throwable;

class ValidationException extends WorthlyApiException
{
    /**
     * @param  array<string, mixed>  $payload
     * @param  array<string, array<int, string>>  $errors
     */
    public function __construct(
        string $message,
        int $status,
        ?Response $response = null,
        array $payload = [],
        public readonly array $errors = [],
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $status, $response, $payload, $previous);
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function errors(): array
    {
        return $this->errors;
    }
}
