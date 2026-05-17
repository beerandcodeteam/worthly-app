<?php

namespace App\Contracts;

interface SecureTokenStorage
{
    public function put(string $token): void;

    public function get(): ?string;

    public function forget(): void;
}
