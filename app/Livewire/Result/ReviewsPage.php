<?php

namespace App\Livewire\Result;

use App\Contracts\SecureTokenStorage;
use App\Livewire\Result\Concerns\LoadsAnalysis;
use App\Services\Worthly\WorthlyApiClient;
use App\Support\ProsConsSplitter;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

class ReviewsPage extends Component
{
    use LoadsAnalysis;

    public function mount(int $analysis, SecureTokenStorage $tokens, WorthlyApiClient $api): mixed
    {
        return $this->loadAnalysis($analysis, $tokens, $api);
    }

    public function productName(): string
    {
        return (string) data_get($this->analysisData, 'product.name', 'Product');
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

    #[Layout('components.layouts.app')]
    #[Title('Worthly · Reviews')]
    public function render(ProsConsSplitter $splitter): mixed
    {
        return view('livewire.result.reviews-page', [
            'productName' => $this->productName(),
            'summary' => $this->summary(),
            'costBenefitAnalysis' => $this->costBenefitAnalysis(),
            'prosCons' => $splitter->split($this->costBenefitAnalysis()),
        ]);
    }
}
