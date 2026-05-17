<?php

namespace App\Livewire\Result\Concerns;

use App\Contracts\SecureTokenStorage;
use App\Services\Worthly\Exceptions\NotFoundException;
use App\Services\Worthly\Exceptions\UnauthorizedException;
use App\Services\Worthly\WorthlyApiClient;
use Illuminate\Support\Facades\Cache;

trait LoadsAnalysis
{
    public int $analysisId = 0;

    /**
     * @var array<string, mixed>
     */
    public array $analysisData = [];

    public bool $loadError = false;

    /**
     * @return mixed Redirect response when the user must be bounced, otherwise null.
     */
    public function loadAnalysis(int $analysisId, SecureTokenStorage $tokens, WorthlyApiClient $api): mixed
    {
        $token = $tokens->get();

        if ($token === null || $token === '') {
            return $this->redirectRoute('login', navigate: false);
        }

        $this->analysisId = $analysisId;

        /** @var array<string, mixed>|null $cached */
        $cached = Cache::get('analyses.'.$analysisId);

        if (is_array($cached) && $cached !== []) {
            $this->analysisData = $cached;

            return null;
        }

        try {
            /** @var array<string, mixed> $fetched */
            $fetched = $api->get('/api/analyses/'.$analysisId);
        } catch (NotFoundException) {
            $this->loadError = true;
            session()->flash('toast', 'Analysis no longer available.');

            return $this->redirectRoute('home', navigate: false);
        } catch (UnauthorizedException) {
            $tokens->forget();
            Cache::forget('auth.user');
            Cache::forget('analyses.recent');
            session()->flash('toast', 'Session expired. Please sign in again.');

            return $this->redirectRoute('login', navigate: false);
        }

        Cache::put('analyses.'.$analysisId, $fetched);

        $this->analysisData = $fetched;

        return null;
    }
}
