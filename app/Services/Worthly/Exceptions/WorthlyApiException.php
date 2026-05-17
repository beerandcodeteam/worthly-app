<?php

namespace App\Services\Worthly\Exceptions;

use Illuminate\Http\Client\Response;
use RuntimeException;
use Throwable;

class WorthlyApiException extends RuntimeException
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(
        string $message,
        public readonly int $status,
        public readonly ?Response $response = null,
        public readonly array $payload = [],
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $status, $previous);
    }

    public function status(): int
    {
        return $this->status;
    }

    /**
     * @return array<string, mixed>
     */
    public function payload(): array
    {
        return $this->payload;
    }
}
