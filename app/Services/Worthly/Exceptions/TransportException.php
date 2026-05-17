<?php

namespace App\Services\Worthly\Exceptions;

use RuntimeException;
use Throwable;

class TransportException extends RuntimeException
{
    public function __construct(string $message = 'Transport error', ?Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
