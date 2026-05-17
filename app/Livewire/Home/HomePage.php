<?php

namespace App\Livewire\Home;

use App\Contracts\SecureTokenStorage;
use App\Livewire\Analyze\Composer;
use App\Services\Worthly\Exceptions\NotFoundException;
use App\Services\Worthly\Exceptions\UnauthorizedException;
use App\Services\Worthly\WorthlyApiClient;
use App\Support\Verdict;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

class HomePage extends Component
{
    public const RECENT_LIMIT = 3;

    public const FREE_PLAN_QUOTA = 50;

    /**
     * @var list<string>
     */
    public const SUGGESTIONS = [
        'Logitech MX Master 3S',
        'Sony WH-1000XM5',
        'Apple AirPods Pro 2',
        'Kindle Paperwhite',
    ];

    public string $composer = '';

    public ?string $toast = null;

    public bool $loadingAnalysisId = false;

    public ?int $openingAnalysisId = null;

    /**
     * @var list<array<string, mixed>>
     */
    public array $recentAnalyses = [];

    public int $totalAnalyses = 0;

    public ?string $firstName = null;

    public function mount(SecureTokenStorage $tokens, WorthlyApiClient $api): mixed
    {
        $token = $tokens->get();

        if ($token === null || $token === '') {
            return $this->redirectRoute('login', navigate: false);
        }

        /** @var array<string, mixed>|null $cachedUser */
        $cachedUser = Cache::get('auth.user');

        $this->firstName = $this->deriveFirstName(is_array($cachedUser) ? $cachedUser : []);

        try {
            /** @var array<string, mixed> $payload */
            $payload = $api->get('/api/analyses', ['page' => 1], unwrapEnvelope: false);
        } catch (UnauthorizedException) {
            return $this->handleSessionExpired($tokens);
        }

        $this->populateRecentAnalyses($payload);

        return null;
    }

    public function prefillSuggestion(string $suggestion): void
    {
        $this->composer = $suggestion;
    }

    public function submit(): mixed
    {
        $query = trim($this->composer);

        if ($query === '') {
            return null;
        }

        if (mb_strlen($query) > Composer::MAX_QUERY_LENGTH) {
            $query = mb_substr($query, 0, Composer::MAX_QUERY_LENGTH);
        }

        return $this->redirectRoute('analyze', [
            'q' => $query,
            'autostart' => 1,
        ], navigate: true);
    }

    public function clearToast(): void
    {
        $this->toast = null;
    }

    public function openAnalysis(int $id, WorthlyApiClient $api, SecureTokenStorage $tokens): mixed
    {
        $this->toast = null;
        $this->openingAnalysisId = $id;

        try {
            $analysis = $api->get('/api/analyses/'.$id);
        } catch (NotFoundException) {
            $this->openingAnalysisId = null;
            $this->toast = 'Analysis no longer available.';

            return null;
        } catch (UnauthorizedException) {
            return $this->handleSessionExpired($tokens);
        }

        Cache::put('analyses.'.$id, $analysis);

        $this->openingAnalysisId = null;

        return $this->redirectRoute('analyses.show', ['analysis' => $id], navigate: true);
    }

    /**
     * @return list<string>
     */
    public function suggestions(): array
    {
        return self::SUGGESTIONS;
    }

    public function planUsageLabel(): string
    {
        return sprintf('%d / %d', $this->totalAnalyses, self::FREE_PLAN_QUOTA);
    }

    /**
     * @return list<array{
     *     id: int,
     *     product_name: string,
     *     verdict: ?string,
     *     verdict_label: ?string,
     *     summary: ?string,
     *     input_type: string,
     *     relative: string,
     * }>
     */
    public function recentAnalysisRows(): array
    {
        return array_map(function (array $analysis): array {
            $decision = (string) data_get($analysis, 'recommendation.decision', '');
            $verdict = $decision !== '' ? Verdict::fromApiDecision($decision) : null;

            return [
                'id' => (int) ($analysis['id'] ?? 0),
                'product_name' => (string) data_get($analysis, 'product.name', 'Untitled analysis'),
                'verdict' => $verdict?->code(),
                'verdict_label' => $verdict?->label(),
                'summary' => $this->shortSummary((string) ($analysis['summary'] ?? '')),
                'input_type' => (string) ($analysis['input_type'] ?? 'text'),
                'relative' => $this->relativeTimestamp((string) ($analysis['created_at'] ?? '')),
            ];
        }, $this->recentAnalyses);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function populateRecentAnalyses(array $payload): void
    {
        /** @var list<array<string, mixed>> $items */
        $items = [];

        if (isset($payload['data']) && is_array($payload['data'])) {
            foreach ($payload['data'] as $row) {
                if (is_array($row)) {
                    $items[] = $row;
                }
            }
        }

        $this->recentAnalyses = array_slice($items, 0, self::RECENT_LIMIT);

        $total = data_get($payload, 'meta.total');
        $this->totalAnalyses = is_int($total) ? $total : count($items);

        Cache::put('analyses.recent', $this->recentAnalyses);
    }

    private function handleSessionExpired(SecureTokenStorage $tokens): mixed
    {
        $tokens->forget();
        Cache::forget('auth.user');
        Cache::forget('analyses.recent');

        session()->flash('toast', 'Session expired. Please sign in again.');

        return $this->redirectRoute('login', navigate: false);
    }

    /**
     * @param  array<string, mixed>  $user
     */
    private function deriveFirstName(array $user): ?string
    {
        $name = (string) ($user['name'] ?? '');

        if ($name === '') {
            return null;
        }

        $first = strtok($name, ' ');

        return $first === false ? $name : $first;
    }

    private function shortSummary(string $summary): ?string
    {
        if ($summary === '') {
            return null;
        }

        return mb_strlen($summary) > 120 ? mb_substr($summary, 0, 117).'…' : $summary;
    }

    private function relativeTimestamp(string $iso): string
    {
        if ($iso === '') {
            return '';
        }

        try {
            return now()->parse($iso)->diffForHumans();
        } catch (\Throwable) {
            return '';
        }
    }

    #[Layout('components.layouts.app')]
    #[Title('Worthly')]
    public function render(): mixed
    {
        return view('livewire.home.home-page', [
            'suggestions' => $this->suggestions(),
            'recentRows' => $this->recentAnalysisRows(),
            'planUsage' => $this->planUsageLabel(),
        ]);
    }
}
