<?php

namespace App\Http\Controllers;

use App\Contracts\SecureTokenStorage;
use App\Services\Worthly\Exceptions\UnauthorizedException;
use App\Services\Worthly\WorthlyApiClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SessionRestoreController extends Controller
{
    public function __invoke(SecureTokenStorage $tokens, WorthlyApiClient $api): RedirectResponse
    {
        $token = $tokens->get();

        if ($token !== null && $token !== '') {
            try {
                $user = $api->get('/api/me');
                Cache::put('auth.user', $user);
                Log::info('worthly.session.restore.success', ['token_present' => true]);
            } catch (UnauthorizedException) {
                $tokens->forget();
                Cache::forget('auth.user');
                Log::info('worthly.session.restore.unauthenticated', ['token_present' => true]);
            }
        }

        return redirect()->route('onboarding');
    }
}
