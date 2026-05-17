<?php

namespace App\Livewire\Analyze;

use App\Contracts\SecureTokenStorage;
use App\Services\Worthly\Exceptions\UnauthorizedException;
use App\Services\Worthly\Exceptions\UpstreamFailureException;
use App\Services\Worthly\Exceptions\ValidationException;
use App\Services\Worthly\Exceptions\WorthlyApiException;
use App\Services\Worthly\WorthlyApiClient;
use Illuminate\Http\Client\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
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
    }

    public function submit(WorthlyApiClient $api, SecureTokenStorage $tokens): mixed
    {
        $this->resetErrorBag();
        $this->upstreamError = false;

        if (! $this->canSubmit()) {
            return null;
        }

        $this->submitting = true;

        return $this->performAnalysisCall($api, $tokens);
    }

    public function runAutoSubmit(WorthlyApiClient $api, SecureTokenStorage $tokens): mixed
    {
        if (! $this->autoSubmit) {
            return null;
        }

        $this->autoSubmit = false;
        $this->resetErrorBag();
        $this->upstreamError = false;
        $this->submitting = true;

        return $this->performAnalysisCall($api, $tokens);
    }

    private function performAnalysisCall(WorthlyApiClient $api, SecureTokenStorage $tokens): mixed
    {
        try {
            if ($this->image instanceof UploadedFile) {
                $data = $this->postImage($api);
            } else {
                $data = $api->post('/api/analyses', [
                    'input_type' => 'text',
                    'query' => $this->query,
                ]);
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
        }

        $id = (int) ($data['id'] ?? 0);

        if ($id > 0) {
            Cache::put('analyses.'.$id, $data);
        }

        $this->submitting = false;

        if ($id <= 0) {
            $this->upstreamError = true;

            return null;
        }

        return $this->redirectRoute('analyses.show', ['analysis' => $id], navigate: true);
    }

    /**
     * @return array<string, mixed>
     */
    private function postImage(WorthlyApiClient $api): array
    {
        $request = $api->pendingRequest()
            ->attach(
                'image',
                file_get_contents($this->image->getRealPath()),
                $this->image->getClientOriginalName(),
            );

        $response = $request->post(
            $this->resolveUrl('/api/analyses'),
            ['input_type' => 'image'],
        );

        return $this->unwrapResponse($response);
    }

    private function resolveUrl(string $path): string
    {
        $base = rtrim((string) config('services.worthly.base_url'), '/');

        return $base.'/'.ltrim($path, '/');
    }

    /**
     * @return array<string, mixed>
     */
    private function unwrapResponse(Response $response): array
    {
        $status = $response->status();

        if ($status >= 200 && $status < 300) {
            /** @var array<string, mixed> $payload */
            $payload = (array) ($response->json() ?? []);

            if (array_key_exists('data', $payload) && is_array($payload['data'])) {
                /** @var array<string, mixed> $data */
                $data = $payload['data'];

                return $data;
            }

            return $payload;
        }

        /** @var array<string, mixed> $payload */
        $payload = (array) ($response->json() ?? []);
        $message = is_string($payload['message'] ?? null) ? $payload['message'] : 'Worthly API error';

        throw match ($status) {
            401 => new UnauthorizedException($message, $status, $response, $payload),
            422 => new ValidationException(
                message: $message,
                status: $status,
                response: $response,
                payload: $payload,
                errors: is_array($payload['errors'] ?? null) ? $payload['errors'] : [],
            ),
            502 => new UpstreamFailureException($message, $status, $response, $payload),
            default => new WorthlyApiException($message, $status, $response, $payload),
        };
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
        return self::STEP_LABELS;
    }

    #[Layout('components.layouts.app')]
    #[Title('New analysis')]
    public function render(): mixed
    {
        return view('livewire.analyze.composer', [
            'steps' => $this->steps(),
        ]);
    }
}
