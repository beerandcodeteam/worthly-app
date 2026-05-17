<?php

namespace App\Support\Storage;

use App\Contracts\SecureTokenStorage;
use Native\Mobile\Facades\SecureStorage;

class NativeSecureTokenStorage implements SecureTokenStorage
{
    private const KEY = 'worthly.auth_token';

    public function put(string $token): void
    {
        SecureStorage::set(self::KEY, $token);
    }

    public function get(): ?string
    {
        return SecureStorage::get(self::KEY);
    }

    public function forget(): void
    {
        SecureStorage::delete(self::KEY);
    }
}
