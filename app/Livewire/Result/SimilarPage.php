<?php

namespace App\Livewire\Result;

use App\Contracts\SecureTokenStorage;
use App\Livewire\Result\Concerns\LoadsAnalysis;
use App\Services\Worthly\WorthlyApiClient;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

class SimilarPage extends Component
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

    public function productPriceRange(): ?string
    {
        $range = data_get($this->analysisData, 'product.estimated_price_range');

        return is_string($range) && $range !== '' ? $range : null;
    }

    /**
     * @return list<array{name: string, reason: ?string, price_reference: ?string}>
     */
    public function similarRows(): array
    {
        $items = data_get($this->analysisData, 'similar_products', []);

        if (! is_array($items)) {
            return [];
        }

        $rows = [];
        foreach ($items as $row) {
            if (! is_array($row)) {
                continue;
            }

            $name = (string) ($row['name'] ?? '');

            if (trim($name) === '') {
                continue;
            }

            $reason = $row['reason'] ?? null;
            $priceReference = $row['price_reference'] ?? null;

            $rows[] = [
                'name' => $name,
                'reason' => is_string($reason) && $reason !== '' ? $reason : null,
                'price_reference' => is_string($priceReference) && $priceReference !== '' ? $priceReference : null,
            ];
        }

        return array_slice($rows, 0, 5);
    }

    #[Layout('components.layouts.app')]
    #[Title('Worthly · Similar products')]
    public function render(): mixed
    {
        $rows = $this->similarRows();

        return view('livewire.result.similar-page', [
            'productName' => $this->productName(),
            'productPriceRange' => $this->productPriceRange(),
            'rows' => $rows,
            'firstSimilar' => $rows[0] ?? null,
        ]);
    }
}
