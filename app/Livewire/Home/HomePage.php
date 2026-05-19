<?php

namespace App\Livewire\Home;

use App\Contracts\SecureTokenStorage;
use App\Livewire\Analyze\Composer;
use App\Services\Worthly\AnalysisSubmitter;
use App\Services\Worthly\Exceptions\NotFoundException;
use App\Services\Worthly\Exceptions\UnauthorizedException;
use App\Services\Worthly\Exceptions\UpstreamFailureException;
use App\Services\Worthly\Exceptions\ValidationException;
use App\Services\Worthly\WorthlyApiClient;
use App\Support\AnalysisPipeline;
use App\Support\Verdict;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class HomePage extends Component
{
    use WithFileUploads;

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

    public ?TemporaryUploadedFile $image = null;

    public bool $submitting = false;

    public bool $upstreamError = false;

    public bool $autoSubmit = false;

    public ?int $pollingAnalysisId = null;

    public string $analysisStatus = '';

    public ?string $currentStep = null;

    public ?string $lastError = null;

    public int $pollStartedAt = 0;

    public bool $analysisFailed = false;

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

    public function updatedImage(): void
    {
        $this->resetErrorBag('image');

        if (! $this->image instanceof UploadedFile) {
            return;
        }

        $extension = strtolower((string) $this->image->getClientOriginalExtension());
        $clientMime = strtolower((string) $this->image->getClientMimeType());

        $extensionAllowed = in_array($extension, Composer::ALLOWED_IMAGE_EXTENSIONS, true);
        $mimeAllowed = in_array($clientMime, Composer::ALLOWED_IMAGE_MIMES, true);

        if (! $extensionAllowed && ! $mimeAllowed) {
            $this->addError('image', 'The image must be a file of type: jpeg, png, webp.');
            $this->image = null;

            return;
        }

        $sizeKb = (int) ceil($this->image->getSize() / 1024);

        if ($sizeKb > Composer::MAX_IMAGE_KB) {
            $this->addError('image', 'The image may not be larger than 8 MB.');
            $this->image = null;
        }
    }

    public function removeImage(): void
    {
        $this->image = null;
        $this->resetErrorBag('image');
    }

    public function hasBothInputs(): bool
    {
        return $this->image !== null && trim($this->composer) !== '';
    }

    public function canSubmit(): bool
    {
        if ($this->submitting) {
            return false;
        }

        if ($this->hasBothInputs()) {
            return false;
        }

        if ($this->image !== null) {
            return true;
        }

        return trim($this->composer) !== '';
    }

    public function dismissUpstreamError(): void
    {
        $this->upstreamError = false;
        $this->analysisFailed = false;
        $this->lastError = null;
    }

    public function retryAnalysis(): void
    {
        $this->dismissUpstreamError();
        $this->submitting = false;
        $this->pollingAnalysisId = null;
        $this->analysisStatus = '';
        $this->currentStep = null;
        $this->pollStartedAt = 0;
    }

    public function submit(): mixed
    {
        $this->resetErrorBag();
        $this->upstreamError = false;

        if (! $this->canSubmit()) {
            return null;
        }

        if ($this->image !== null) {
            $this->submitting = true;
            $this->autoSubmit = true;

            return null;
        }

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

    public function runImageAnalysis(AnalysisSubmitter $submitter, SecureTokenStorage $tokens): mixed
    {
        if (! $this->autoSubmit) {
            return null;
        }

        $this->autoSubmit = false;

        if ($this->image === null) {
            $this->submitting = false;

            return null;
        }

        return $this->performImageAnalysis($submitter, $tokens);
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

    /**
     * @return list<string>
     */
    public function steps(): array
    {
        return AnalysisPipeline::stepLabels();
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
                'product_name' => $this->resolveProductName($analysis),
                'product_image_url' => $this->resolveProductImageUrl($analysis),
                'verdict' => $verdict?->code(),
                'verdict_label' => $verdict?->label(),
                'summary' => $this->resolveSummary($analysis),
                'input_type' => (string) ($analysis['input_type'] ?? 'text'),
                'relative' => $this->relativeTimestamp((string) ($analysis['created_at'] ?? '')),
            ];
        }, $this->recentAnalyses);
    }

    private function performImageAnalysis(AnalysisSubmitter $submitter, SecureTokenStorage $tokens): mixed
    {
        try {
            $data = $submitter->submitImage($this->image);
        } catch (ValidationException $exception) {
            $this->submitting = false;

            foreach ($exception->errors() as $field => $messages) {
                if (is_array($messages) && isset($messages[0])) {
                    $this->addError($field, (string) $messages[0]);
                }
            }

            return null;
        } catch (UpstreamFailureException) {
            $this->submitting = false;
            $this->upstreamError = true;

            return null;
        } catch (UnauthorizedException) {
            return $this->handleSessionExpired($tokens);
        } catch (\Throwable $e) {
            Log::error('worthly.analysis.submit.exception', [
                'message' => $e->getMessage(),
                'exception' => $e::class,
            ]);

            $this->submitting = false;
            $this->upstreamError = true;

            return null;
        }

        $id = (int) ($data['id'] ?? 0);

        if ($id <= 0) {
            $this->submitting = false;
            $this->upstreamError = true;

            return null;
        }

        Cache::put('analyses.'.$id, $data);

        $this->pollingAnalysisId = $id;
        $this->analysisStatus = (string) ($data['status'] ?? AnalysisPipeline::STATUS_PENDING);
        $this->currentStep = is_string($data['current_step'] ?? null) ? $data['current_step'] : null;
        $this->lastError = is_string($data['last_error'] ?? null) ? $data['last_error'] : null;
        $this->pollStartedAt = time();

        if ($this->analysisStatus === AnalysisPipeline::STATUS_COMPLETED) {
            return $this->redirectRoute('analyses.show', ['analysis' => $id], navigate: true);
        }

        if ($this->analysisStatus === AnalysisPipeline::STATUS_FAILED) {
            $this->analysisFailed = true;
        }

        return null;
    }

    public function pollAnalysisStatus(WorthlyApiClient $api, SecureTokenStorage $tokens): mixed
    {
        if ($this->pollingAnalysisId === null) {
            return null;
        }

        if (AnalysisPipeline::isTerminal($this->analysisStatus)) {
            return null;
        }

        try {
            $data = $api->get('/api/analyses/'.$this->pollingAnalysisId);
        } catch (NotFoundException) {
            $this->analysisStatus = AnalysisPipeline::STATUS_FAILED;
            $this->analysisFailed = true;
            $this->lastError = 'Analysis is no longer available.';

            return null;
        } catch (UnauthorizedException) {
            return $this->handleSessionExpired($tokens);
        }

        $this->analysisStatus = (string) ($data['status'] ?? $this->analysisStatus);
        $this->currentStep = is_string($data['current_step'] ?? null) ? $data['current_step'] : null;
        $this->lastError = is_string($data['last_error'] ?? null) ? $data['last_error'] : null;

        if ($this->analysisStatus === AnalysisPipeline::STATUS_COMPLETED) {
            Cache::put('analyses.'.$this->pollingAnalysisId, $data);

            return $this->redirectRoute('analyses.show', ['analysis' => $this->pollingAnalysisId], navigate: true);
        }

        if ($this->analysisStatus === AnalysisPipeline::STATUS_FAILED) {
            $this->analysisFailed = true;
        }

        return null;
    }

    /**
     * @return list<array{key: string, label: string, state: 'done'|'active'|'idle'}>
     */
    public function pipelineSteps(): array
    {
        return array_map(function (array $step, int $index): array {
            return [
                'key' => $step['key'],
                'label' => $step['label'],
                'state' => AnalysisPipeline::stepState($index, $this->analysisStatus, $this->currentStep),
            ];
        }, AnalysisPipeline::STEPS, array_keys(AnalysisPipeline::STEPS));
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

    /**
     * @param  array<string, mixed>  $analysis
     */
    private function resolveProductName(array $analysis): string
    {
        $candidates = [
            data_get($analysis, 'product.name'),
            $analysis['product_name'] ?? null,
            $analysis['name'] ?? null,
        ];

        foreach ($candidates as $candidate) {
            if (is_string($candidate) && trim($candidate) !== '') {
                return trim($candidate);
            }
        }

        return 'Untitled analysis';
    }

    /**
     * @param  array<string, mixed>  $analysis
     */
    private function resolveProductImageUrl(array $analysis): ?string
    {
        $candidates = [
            $analysis['product_image_url'] ?? null,
            data_get($analysis, 'product.image_url'),
            $analysis['image_url'] ?? null,
        ];

        foreach ($candidates as $candidate) {
            if (is_string($candidate) && trim($candidate) !== '') {
                return trim($candidate);
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $analysis
     */
    private function resolveSummary(array $analysis): ?string
    {
        $candidates = [
            $analysis['summary'] ?? null,
            data_get($analysis, 'recommendation.reason'),
        ];

        foreach ($candidates as $candidate) {
            if (is_string($candidate) && trim($candidate) !== '') {
                $trimmed = trim($candidate);

                return mb_strlen($trimmed) > 120 ? mb_substr($trimmed, 0, 117).'…' : $trimmed;
            }
        }

        return null;
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
            'steps' => $this->steps(),
            'pipelineSteps' => $this->pipelineSteps(),
        ]);
    }
}
