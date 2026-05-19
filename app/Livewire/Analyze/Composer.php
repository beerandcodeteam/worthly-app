<?php

namespace App\Livewire\Analyze;

use App\Contracts\SecureTokenStorage;
use App\Services\Worthly\AnalysisSubmitter;
use App\Services\Worthly\Exceptions\NotFoundException;
use App\Services\Worthly\Exceptions\UnauthorizedException;
use App\Services\Worthly\Exceptions\UpstreamFailureException;
use App\Services\Worthly\Exceptions\ValidationException;
use App\Services\Worthly\WorthlyApiClient;
use App\Support\AnalysisPipeline;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class Composer extends Component
{
    use WithFileUploads;

    public const MAX_QUERY_LENGTH = 1000;

    public const MAX_IMAGE_KB = 8192;

    /**
     * @var list<string>
     */
    public const ALLOWED_IMAGE_MIMES = ['image/jpeg', 'image/png', 'image/webp'];

    /**
     * @var list<string>
     */
    public const ALLOWED_IMAGE_EXTENSIONS = ['jpeg', 'jpg', 'png', 'webp'];

    /**
     * @deprecated Use {@see AnalysisPipeline::stepLabels()} instead.
     *
     * @var list<string>
     */
    public const STEP_LABELS = [
        'Identifying product',
        'Searching the web',
        'Reading reviews',
        'Comparing alternatives',
        'Forming a verdict',
    ];

    public string $query = '';

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

    public function mount(SecureTokenStorage $tokens): mixed
    {
        $token = $tokens->get();

        if ($token === null || $token === '') {
            return $this->redirectRoute('login', navigate: false);
        }

        $prefill = trim((string) request()->query('q', ''));

        if ($prefill !== '') {
            $this->query = mb_substr($prefill, 0, self::MAX_QUERY_LENGTH);
        }

        if ((string) request()->query('autostart') === '1' && trim($this->query) !== '') {
            $this->autoSubmit = true;
            $this->submitting = true;
        }

        return null;
    }

    public function updatedQuery(): void
    {
        if (mb_strlen($this->query) > self::MAX_QUERY_LENGTH) {
            $this->query = mb_substr($this->query, 0, self::MAX_QUERY_LENGTH);
        }
    }

    public function updatedImage(): void
    {
        $this->resetErrorBag('image');

        if (! $this->image instanceof UploadedFile) {
            return;
        }

        $extension = strtolower((string) $this->image->getClientOriginalExtension());
        $clientMime = strtolower((string) $this->image->getClientMimeType());

        $extensionAllowed = in_array($extension, self::ALLOWED_IMAGE_EXTENSIONS, true);
        $mimeAllowed = in_array($clientMime, self::ALLOWED_IMAGE_MIMES, true);

        if (! $extensionAllowed && ! $mimeAllowed) {
            $this->addError('image', 'The image must be a file of type: jpeg, png, webp.');
            $this->image = null;

            return;
        }

        $sizeKb = (int) ceil($this->image->getSize() / 1024);

        if ($sizeKb > self::MAX_IMAGE_KB) {
            $this->addError('image', 'The image may not be larger than 8 MB.');
            $this->image = null;

            return;
        }
    }

    public function removeImage(): void
    {
        $this->image = null;
        $this->resetErrorBag('image');
    }

    public function hasBothInputs(): bool
    {
        return $this->image !== null && trim($this->query) !== '';
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

        return trim($this->query) !== '';
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

    public function submit(AnalysisSubmitter $submitter, SecureTokenStorage $tokens): mixed
    {
        $this->resetErrorBag();
        $this->upstreamError = false;

        if (! $this->canSubmit()) {
            return null;
        }

        $this->submitting = true;

        return $this->performAnalysisCall($submitter, $tokens);
    }

    public function runAutoSubmit(AnalysisSubmitter $submitter, SecureTokenStorage $tokens): mixed
    {
        if (! $this->autoSubmit) {
            return null;
        }

        $this->autoSubmit = false;
        $this->resetErrorBag();
        $this->upstreamError = false;
        $this->submitting = true;

        return $this->performAnalysisCall($submitter, $tokens);
    }

    private function performAnalysisCall(AnalysisSubmitter $submitter, SecureTokenStorage $tokens): mixed
    {
        try {
            if ($this->image instanceof UploadedFile) {
                $data = $submitter->submitImage($this->image);
            } else {
                $data = $submitter->submitText($this->query);
            }
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

    private function handleSessionExpired(SecureTokenStorage $tokens): mixed
    {
        $tokens->forget();
        Cache::forget('auth.user');
        Cache::forget('analyses.recent');

        session()->flash('toast', 'Session expired. Please sign in again.');

        return $this->redirectRoute('login', navigate: false);
    }

    /**
     * @return list<string>
     */
    public function steps(): array
    {
        return AnalysisPipeline::stepLabels();
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

    #[Layout('components.layouts.app')]
    #[Title('New analysis')]
    public function render(): mixed
    {
        return view('livewire.analyze.composer', [
            'steps' => $this->steps(),
            'pipelineSteps' => $this->pipelineSteps(),
        ]);
    }
}
