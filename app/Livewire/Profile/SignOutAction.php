<?php

namespace App\Livewire\Profile;

use App\Contracts\SecureTokenStorage;
use App\Services\Worthly\Exceptions\UnauthorizedException;
use App\Services\Worthly\WorthlyApiClient;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class SignOutAction extends Component
{
    public bool $confirming = false;

    public function confirm(): void
    {
        $this->confirming = true;
    }

    public function cancel(): void
    {
        $this->confirming = false;
    }

    public function signOut(WorthlyApiClient $api, SecureTokenStorage $tokens): mixed
    {
        if (! $this->confirming) {
            $this->confirming = true;

            return null;
        }

        try {
            $api->post('/api/logout');
        } catch (UnauthorizedException) {
            // Server already considers the token invalid; still proceed with local cleanup.
        }

        $tokens->forget();
        Cache::forget('auth.user');
        Cache::forget('analyses.recent');

        $this->confirming = false;

        return $this->redirectRoute('login', navigate: true);
    }

    public function render(): mixed
    {
        return view('livewire.profile.sign-out-action');
    }
}
