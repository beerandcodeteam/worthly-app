<?php

namespace App\Livewire\Profile;

use App\Contracts\SecureTokenStorage;
use App\Services\Worthly\Exceptions\UnauthorizedException;
use App\Services\Worthly\WorthlyApiClient;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

class ProfilePage extends Component
{
    public const FREE_PLAN_QUOTA = 50;

    public const SAVED_PRODUCTS_PLACEHOLDER = 0;

    public const MONEY_SAVED_PLACEHOLDER = '—';

    public const UPGRADE_TOOLTIP = 'Coming soon';

    public ?string $name = null;

    public ?string $email = null;

    public int $totalAnalyses = 0;

    public bool $loadingProfile = true;

    public bool $refreshing = false;

    public function mount(SecureTokenStorage $tokens, WorthlyApiClient $api): mixed
    {
        $token = $tokens->get();

        if ($token === null || $token === '') {
            return $this->redirectRoute('login', navigate: false);
        }

        return $this->loadProfile($tokens, $api);
    }

    public function refresh(SecureTokenStorage $tokens, WorthlyApiClient $api): mixed
    {
        $this->refreshing = true;

        Cache::forget('auth.user');
        Cache::forget('profile.usage.total');

        return $this->loadProfile($tokens, $api);
    }

    public function savedProducts(): int
    {
        return self::SAVED_PRODUCTS_PLACEHOLDER;
    }

    public function moneySaved(): string
    {
        return self::MONEY_SAVED_PLACEHOLDER;
    }

    public function avatarInitial(): string
    {
        if ($this->name === null || trim($this->name) === '') {
            return '?';
        }

        $first = mb_substr(trim($this->name), 0, 1);

        return mb_strtoupper($first);
    }

    public function planUsageLabel(): string
    {
        return sprintf('%d / %d', $this->totalAnalyses, self::FREE_PLAN_QUOTA);
    }

    public function planUsagePercent(): int
    {
        if (self::FREE_PLAN_QUOTA <= 0) {
            return 0;
        }

        $percent = (int) round(($this->totalAnalyses / self::FREE_PLAN_QUOTA) * 100);

        return max(0, min(100, $percent));
    }

    public function upgradeCtaDisabled(): bool
    {
        return true;
    }

    public function upgradeTooltip(): string
    {
        return self::UPGRADE_TOOLTIP;
    }

    /**
     * The Profile page never blocks navigation or submissions based on the usage indicator.
     */
    public function submissionsBlocked(): bool
    {
        return false;
    }

    private function loadProfile(SecureTokenStorage $tokens, WorthlyApiClient $api): mixed
    {
        $this->loadingProfile = true;

        /** @var array<string, mixed>|null $cachedUser */
        $cachedUser = Cache::get('auth.user');

        if (is_array($cachedUser) && ($cachedUser['email'] ?? null) !== null) {
            $this->applyUser($cachedUser);
        } else {
            try {
                /** @var array<string, mixed> $user */
                $user = $api->get('/api/me');
            } catch (UnauthorizedException) {
                return $this->handleSessionExpired($tokens);
            }

            $this->applyUser($user);
            Cache::put('auth.user', $user);
        }

        /** @var int|null $cachedTotal */
        $cachedTotal = Cache::get('profile.usage.total');

        if (is_int($cachedTotal)) {
            $this->totalAnalyses = $cachedTotal;
        } else {
            try {
                /** @var array<string, mixed> $payload */
                $payload = $api->get('/api/analyses', ['page' => 1], unwrapEnvelope: false);
            } catch (UnauthorizedException) {
                return $this->handleSessionExpired($tokens);
            }

            $total = data_get($payload, 'meta.total');
            $this->totalAnalyses = is_int($total) ? $total : 0;

            Cache::put('profile.usage.total', $this->totalAnalyses);
        }

        $this->loadingProfile = false;
        $this->refreshing = false;

        return null;
    }

    /**
     * @param  array<string, mixed>  $user
     */
    private function applyUser(array $user): void
    {
        $name = $user['name'] ?? null;
        $email = $user['email'] ?? null;

        $this->name = is_string($name) ? $name : null;
        $this->email = is_string($email) ? $email : null;
    }

    private function handleSessionExpired(SecureTokenStorage $tokens): mixed
    {
        $tokens->forget();
        Cache::forget('auth.user');
        Cache::forget('analyses.recent');
        Cache::forget('profile.usage.total');

        session()->flash('toast', 'Session expired. Please sign in again.');

        return $this->redirectRoute('login', navigate: false);
    }

    #[Layout('components.layouts.app')]
    #[Title('Worthly · Profile')]
    public function render(): mixed
    {
        return view('livewire.profile.profile-page', [
            'avatarInitial' => $this->avatarInitial(),
            'planUsageLabel' => $this->planUsageLabel(),
            'planUsagePercent' => $this->planUsagePercent(),
            'savedProducts' => $this->savedProducts(),
            'moneySaved' => $this->moneySaved(),
            'upgradeTooltip' => $this->upgradeTooltip(),
            'upgradeDisabled' => $this->upgradeCtaDisabled(),
        ]);
    }
}
