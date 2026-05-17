<?php

namespace App\Livewire\Result;

use App\Contracts\SecureTokenStorage;
use App\Livewire\Result\Concerns\LoadsAnalysis;
use App\Services\Worthly\WorthlyApiClient;
use App\Support\ProsConsSplitter;
use App\Support\Verdict;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

class ResultPage extends Component
{
    use LoadsAnalysis;

    public bool $shouldLoadImage = false;

    public bool $imageLoaded = false;

    public bool $imageMissing = false;

    public ?string $imageDataUrl = null;

    public function mount(int $analysis, SecureTokenStorage $tokens, WorthlyApiClient $api): mixed
    {
        $redirect = $this->loadAnalysis($analysis, $tokens, $api);

        if ($redirect !== null) {
            return $redirect;
        }

        $this->shouldLoadImage = (string) data_get($this->analysisData, 'input_type') === 'image';

        return null;
    }

    public function loadImage(SecureTokenStorage $tokens, WorthlyApiClient $api): mixed
    {
        if (! $this->shouldLoadImage || $this->imageLoaded || $this->imageMissing) {
            return null;
        }

        $base = rtrim((string) config('services.worthly.base_url'), '/');
        $url = $base.'/api/analyses/'.$this->analysisId.'/image';

        $response = $api->pendingRequest()->get($url);
        $status = $response->status();

        if ($status === 401) {
            $tokens->forget();
            Cache::forget('auth.user');
            Cache::forget('analyses.recent');
            Cache::forget('analyses.'.$this->analysisId);
            session()->flash('toast', 'Session expired. Please sign in again.');

            return $this->redirectRoute('login', navigate: false);
        }

        if ($status === 404) {
            $this->imageMissing = true;

            return null;
        }

        if ($status >= 200 && $status < 300) {
            $mime = (string) ($response->header('Content-Type') ?: 'image/jpeg');
            $this->imageDataUrl = 'data:'.$mime.';base64,'.base64_encode($response->body());
            $this->imageLoaded = true;
        }

        return null;
    }

    public function newAnalysis(): mixed
    {
        return $this->redirectRoute('home', navigate: true);
    }

    public function seeBestOffer(): mixed
    {
        return $this->redirectRoute('analyses.offers', ['analysis' => $this->analysisId], navigate: true);
    }

    public function verdict(): ?Verdict
    {
        $decision = (string) data_get($this->analysisData, 'recommendation.decision', '');

        if ($decision === '') {
            return null;
        }

        return Verdict::fromApiDecision($decision);
    }

    public function decision(): ?string
    {
        $decision = (string) data_get($this->analysisData, 'recommendation.decision', '');

        return $decision === '' ? null : $decision;
    }

    public function tldr(): string
    {
        $tldr = (string) data_get($this->analysisData, 'recommendation.reason', '');

        return trim($tldr);
    }

    public function isPriceConditional(): bool
    {
        return $this->decision() === 'buy_if_price_is_good';
    }

    public function productName(): string
    {
        return (string) data_get($this->analysisData, 'product.name', 'Untitled analysis');
    }

    public function productCategory(): ?string
    {
        $category = data_get($this->analysisData, 'product.category');

        return is_string($category) && $category !== '' ? $category : null;
    }

    public function estimatedPriceRange(): ?string
    {
        $range = data_get($this->analysisData, 'product.estimated_price_range');

        return is_string($range) && $range !== '' ? $range : null;
    }

    public function summary(): ?string
    {
        $summary = data_get($this->analysisData, 'summary');

        return is_string($summary) && trim($summary) !== '' ? $summary : null;
    }

    public function costBenefitAnalysis(): ?string
    {
        $text = data_get($this->analysisData, 'cost_benefit_analysis');

        return is_string($text) && trim($text) !== '' ? $text : null;
    }

    /**
     * @return array{pros: list<string>, cons: list<string>, fallback: ?string}
     */
    public function prosCons(ProsConsSplitter $splitter): array
    {
        return $splitter->split($this->costBenefitAnalysis());
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function similarProducts(): array
    {
        $items = data_get($this->analysisData, 'similar_products', []);

        if (! is_array($items)) {
            return [];
        }

        $clean = [];
        foreach ($items as $row) {
            if (is_array($row)) {
                $clean[] = $row;
            }
        }

        return array_slice($clean, 0, 5);
    }

    public function hasSimilar(): bool
    {
        return $this->similarProducts() !== [];
    }

    public function hasReviewsContent(): bool
    {
        return $this->summary() !== null || $this->costBenefitAnalysis() !== null;
    }

    public function hasOffersContent(): bool
    {
        return $this->estimatedPriceRange() !== null || $this->hasSimilar();
    }

    #[Layout('components.layouts.app')]
    #[Title('Worthly · Result')]
    public function render(ProsConsSplitter $splitter): mixed
    {
        return view('livewire.result.result-page', [
            'verdict' => $this->verdict(),
            'decision' => $this->decision(),
            'tldr' => $this->tldr(),
            'isPriceConditional' => $this->isPriceConditional(),
            'productName' => $this->productName(),
            'productCategory' => $this->productCategory(),
            'estimatedPriceRange' => $this->estimatedPriceRange(),
            'summary' => $this->summary(),
            'costBenefitAnalysis' => $this->costBenefitAnalysis(),
            'prosCons' => $splitter->split($this->costBenefitAnalysis()),
            'similar' => $this->similarProducts(),
            'similarCount' => count($this->similarProducts()),
            'hasSimilar' => $this->hasSimilar(),
            'hasReviewsContent' => $this->hasReviewsContent(),
            'hasOffersContent' => $this->hasOffersContent(),
            'shouldLoadImage' => $this->shouldLoadImage,
            'imageLoaded' => $this->imageLoaded,
            'imageMissing' => $this->imageMissing,
            'imageDataUrl' => $this->imageDataUrl,
        ]);
    }
}
