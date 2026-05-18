<?php

namespace App\Livewire\History;

use App\Contracts\SecureTokenStorage;
use App\Services\Worthly\Exceptions\NotFoundException;
use App\Services\Worthly\Exceptions\UnauthorizedException;
use App\Services\Worthly\WorthlyApiClient;
use App\Support\Verdict;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

class HistoryPage extends Component
{
    public const PER_PAGE = 15;

    /**
     * @var list<string>
     */
    public const FILTERS = ['all', 'buy', 'wait', 'skip'];

    /**
     * @var list<array<string, mixed>>
     */
    public array $rows = [];

    public int $currentPage = 0;

    public bool $hasNextPage = false;

    public bool $loadingPage1 = true;

    public bool $loadingMore = false;

    public bool $refreshing = false;

    public string $filter = 'all';

    public ?string $toast = null;

    public ?int $openingAnalysisId = null;

    public ?int $confirmDeleteId = null;

    public ?int $deletingAnalysisId = null;

    public function mount(SecureTokenStorage $tokens, WorthlyApiClient $api): mixed
    {
        $token = $tokens->get();

        if ($token === null || $token === '') {
            return $this->redirectRoute('login', navigate: false);
        }

        return $this->fetchPage(1, $tokens, $api, replace: true);
    }

    public function loadMore(SecureTokenStorage $tokens, WorthlyApiClient $api): mixed
    {
        if (! $this->hasNextPage || $this->loadingMore || $this->loadingPage1) {
            return null;
        }

        return $this->fetchPage($this->currentPage + 1, $tokens, $api, replace: false);
    }

    public function refresh(SecureTokenStorage $tokens, WorthlyApiClient $api): mixed
    {
        $this->refreshing = true;
        $this->toast = null;

        return $this->fetchPage(1, $tokens, $api, replace: true);
    }

    public function setFilter(string $filter): void
    {
        if (! in_array($filter, self::FILTERS, true)) {
            return;
        }

        $this->filter = $filter;
    }

    public function clearFilter(): void
    {
        $this->filter = 'all';
    }

    public function clearToast(): void
    {
        $this->toast = null;
    }

    public function requestDelete(int $id): void
    {
        $this->toast = null;
        $this->confirmDeleteId = $id;
    }

    public function cancelDelete(): void
    {
        $this->confirmDeleteId = null;
    }

    public function confirmDelete(WorthlyApiClient $api, SecureTokenStorage $tokens): mixed
    {
        if ($this->confirmDeleteId === null) {
            return null;
        }

        $id = $this->confirmDeleteId;
        $this->deletingAnalysisId = $id;

        try {
            $api->delete('/api/analyses/'.$id);
        } catch (NotFoundException) {
            $this->removeRow($id);
            $this->confirmDeleteId = null;
            $this->deletingAnalysisId = null;
            $this->toast = 'Analysis no longer available.';

            return null;
        } catch (UnauthorizedException) {
            return $this->handleSessionExpired($tokens);
        }

        $this->removeRow($id);
        $this->confirmDeleteId = null;
        $this->deletingAnalysisId = null;
        Cache::forget('analyses.'.$id);

        return null;
    }

    public function openAnalysis(int $id, WorthlyApiClient $api, SecureTokenStorage $tokens): mixed
    {
        $this->toast = null;
        $this->openingAnalysisId = $id;

        $row = $this->findRow($id);

        if ($row !== null) {
            Cache::put('analyses.hero-skeleton.'.$id, $this->heroSkeleton($row));
        }

        try {
            $analysis = $api->get('/api/analyses/'.$id);
        } catch (NotFoundException) {
            $this->openingAnalysisId = null;
            $this->removeRow($id);
            Cache::forget('analyses.hero-skeleton.'.$id);
            $this->toast = 'Analysis no longer available.';

            return null;
        } catch (UnauthorizedException) {
            return $this->handleSessionExpired($tokens);
        }

        Cache::put('analyses.'.$id, $analysis);
        Cache::forget('analyses.hero-skeleton.'.$id);

        $this->openingAnalysisId = null;

        return $this->redirectRoute('analyses.show', ['analysis' => $id], navigate: true);
    }

    public function startNewAnalysis(): mixed
    {
        return $this->redirectRoute('home', navigate: true);
    }

    private function fetchPage(int $page, SecureTokenStorage $tokens, WorthlyApiClient $api, bool $replace): mixed
    {
        if ($replace) {
            $this->loadingPage1 = true;
        } else {
            $this->loadingMore = true;
        }

        try {
            /** @var array<string, mixed> $payload */
            $payload = $api->get('/api/analyses', ['page' => $page], unwrapEnvelope: false);
        } catch (UnauthorizedException) {
            return $this->handleSessionExpired($tokens);
        }

        $items = [];

        if (isset($payload['data']) && is_array($payload['data'])) {
            foreach ($payload['data'] as $row) {
                if (is_array($row)) {
                    $items[] = $row;
                }
            }
        }

        if ($replace) {
            $this->rows = $items;
        } else {
            $this->rows = array_values(array_merge($this->rows, $items));
        }

        $this->currentPage = $page;

        $next = data_get($payload, 'links.next');
        $this->hasNextPage = is_string($next) && $next !== '';

        $this->loadingPage1 = false;
        $this->loadingMore = false;
        $this->refreshing = false;

        return null;
    }

    private function removeRow(int $id): void
    {
        $this->rows = array_values(array_filter(
            $this->rows,
            fn (array $row): bool => (int) ($row['id'] ?? 0) !== $id,
        ));
    }

    /**
     * @return array<string, mixed>|null
     */
    private function findRow(int $id): ?array
    {
        foreach ($this->rows as $row) {
            if ((int) ($row['id'] ?? 0) === $id) {
                return $row;
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    private function heroSkeleton(array $row): array
    {
        return [
            'id' => (int) ($row['id'] ?? 0),
            'product' => is_array($row['product'] ?? null) ? $row['product'] : [],
            'recommendation' => is_array($row['recommendation'] ?? null) ? $row['recommendation'] : [],
            'input_type' => (string) ($row['input_type'] ?? 'text'),
            'created_at' => (string) ($row['created_at'] ?? ''),
        ];
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
     * @return list<array<string, mixed>>
     */
    public function filteredRows(): array
    {
        if ($this->filter === 'all') {
            return $this->normalizeRows($this->rows);
        }

        $filtered = array_values(array_filter($this->rows, function (array $row): bool {
            return $this->rowVerdictBucket($row) === $this->filter;
        }));

        return $this->normalizeRows($filtered);
    }

    /**
     * @return array<string, list<array<string, mixed>>>
     */
    public function groupedRows(): array
    {
        $groups = [
            'today' => [],
            'yesterday' => [],
            'this_week' => [],
            'earlier' => [],
        ];

        foreach ($this->filteredRows() as $row) {
            $bucket = $this->dayBucket((string) ($row['created_at'] ?? ''));
            $groups[$bucket][] = $row;
        }

        return $groups;
    }

    public function hasAnyRows(): bool
    {
        return $this->rows !== [];
    }

    public function showInitialEmptyState(): bool
    {
        if ($this->loadingPage1) {
            return false;
        }

        return $this->rows === [];
    }

    public function showFilteredEmptyState(): bool
    {
        if ($this->loadingPage1) {
            return false;
        }

        if ($this->rows === []) {
            return false;
        }

        return $this->filteredRows() === [];
    }

    public function filteredEmptyLabel(): string
    {
        return match ($this->filter) {
            'buy' => 'No Buy analyses yet',
            'wait' => 'No Wait analyses yet',
            'skip' => 'No Skip analyses yet',
            default => '',
        };
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     * @return list<array<string, mixed>>
     */
    private function normalizeRows(array $rows): array
    {
        return array_map(function (array $row): array {
            $decision = (string) data_get($row, 'recommendation.decision', '');
            $verdict = $decision !== '' ? Verdict::fromApiDecision($decision) : null;

            return [
                'id' => (int) ($row['id'] ?? 0),
                'product_name' => $this->resolveProductName($row),
                'verdict' => $verdict?->code(),
                'verdict_label' => $verdict?->label(),
                'verdict_bucket' => $verdict !== null ? strtolower($verdict->code()) : null,
                'input_type' => (string) ($row['input_type'] ?? 'text'),
                'created_at' => (string) ($row['created_at'] ?? ''),
                'relative' => $this->relativeTimestamp((string) ($row['created_at'] ?? '')),
            ];
        }, $rows);
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function resolveProductName(array $row): string
    {
        $candidates = [
            data_get($row, 'product.name'),
            $row['product_name'] ?? null,
            $row['name'] ?? null,
        ];

        foreach ($candidates as $candidate) {
            if (is_string($candidate) && trim($candidate) !== '') {
                return trim($candidate);
            }
        }

        return 'Untitled analysis';
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function rowVerdictBucket(array $row): ?string
    {
        $decision = (string) data_get($row, 'recommendation.decision', '');

        if ($decision === '') {
            return null;
        }

        return strtolower(Verdict::fromApiDecision($decision)->code());
    }

    private function dayBucket(string $iso): string
    {
        if ($iso === '') {
            return 'earlier';
        }

        try {
            $when = Carbon::parse($iso);
        } catch (\Throwable) {
            return 'earlier';
        }

        $now = now();

        if ($when->isSameDay($now)) {
            return 'today';
        }

        if ($when->isSameDay($now->copy()->subDay())) {
            return 'yesterday';
        }

        if ($when->greaterThanOrEqualTo($now->copy()->subDays(7)->startOfDay())) {
            return 'this_week';
        }

        return 'earlier';
    }

    private function relativeTimestamp(string $iso): string
    {
        if ($iso === '') {
            return '';
        }

        try {
            return Carbon::parse($iso)->diffForHumans();
        } catch (\Throwable) {
            return '';
        }
    }

    /**
     * @return list<array{key: string, label: string}>
     */
    public function filterChips(): array
    {
        return [
            ['key' => 'all', 'label' => 'All'],
            ['key' => 'buy', 'label' => 'Buy'],
            ['key' => 'wait', 'label' => 'Wait'],
            ['key' => 'skip', 'label' => 'Skip'],
        ];
    }

    /**
     * @return list<array{key: string, label: string}>
     */
    public function groupLabels(): array
    {
        return [
            ['key' => 'today', 'label' => 'Today'],
            ['key' => 'yesterday', 'label' => 'Yesterday'],
            ['key' => 'this_week', 'label' => 'This week'],
            ['key' => 'earlier', 'label' => 'Earlier'],
        ];
    }

    #[Layout('components.layouts.app')]
    #[Title('Worthly · History')]
    public function render(): mixed
    {
        return view('livewire.history.history-page', [
            'filterChips' => $this->filterChips(),
            'groupLabels' => $this->groupLabels(),
            'grouped' => $this->groupedRows(),
            'filteredCount' => count($this->filteredRows()),
            'showInitialEmpty' => $this->showInitialEmptyState(),
            'showFilteredEmpty' => $this->showFilteredEmptyState(),
            'filteredEmptyLabel' => $this->filteredEmptyLabel(),
        ]);
    }
}
