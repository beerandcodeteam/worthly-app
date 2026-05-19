<?php

namespace App\Support\Storage;

use App\Contracts\SecureTokenStorage;
use Illuminate\Support\Facades\Log;
use Native\Mobile\Facades\SecureStorage;
use Throwable;

class NativeSecureTokenStorage implements SecureTokenStorage
{
    private const KEY = 'worthly.auth_token';

    public function put(string $token): void
    {
        try {
            $stored = (bool) SecureStorage::set(self::KEY, $token);

            if (! $stored) {
                Log::error('worthly.secure_storage.put.failed', [
                    'reason' => 'SecureStorage::set returned false — keychain bridge unavailable',
                ]);
            }
        } catch (Throwable $e) {
            Log::error('worthly.secure_storage.put.exception', [
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function get(): ?string
    {
        try {
            $token = SecureStorage::get(self::KEY);

            return is_string($token) && $token !== '' ? $token : null;
        } catch (Throwable $e) {
            Log::error('worthly.secure_storage.get.exception', [
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    public function forget(): void
    {
        try {
            SecureStorage::delete(self::KEY);
        } catch (Throwable $e) {
            Log::warning('worthly.secure_storage.delete.exception', [
                'message' => $e->getMessage(),
            ]);
        }
    }
}
