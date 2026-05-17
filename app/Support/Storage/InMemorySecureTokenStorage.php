<?php

namespace App\Support\Storage;

use App\Contracts\SecureTokenStorage;

class InMemorySecureTokenStorage implements SecureTokenStorage
{
    private ?string $token = null;

    public function put(string $token): void
    {
        $this->token = $token;
    }

    public function get(): ?string
    {
        return $this->token;
    }

    public function forget(): void
    {
        $this->token = null;
    }
}
