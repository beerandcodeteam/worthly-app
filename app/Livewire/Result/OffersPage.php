<?php

namespace App\Livewire\Result;

use App\Contracts\SecureTokenStorage;
use App\Livewire\Result\Concerns\LoadsAnalysis;
use App\Services\Worthly\WorthlyApiClient;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

class OffersPage extends Component
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

    public function priceReference(): ?string
    {
        $range = data_get($this->analysisData, 'product.estimated_price_range');

        return is_string($range) && $range !== '' ? $range : null;
    }

    public function priceGuidance(): ?string
    {
        $reason = data_get($this->analysisData, 'recommendation.reason');

        return is_string($reason) && trim($reason) !== '' ? $reason : null;
    }

    /**
     * @return list<array{name: string, reason: ?string, price_reference: ?string, sort_key: float, has_price: bool}>
     */
    public function alternatives(): array
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

            $priceReference = $row['price_reference'] ?? null;
            $priceReference = is_string($priceReference) && $priceReference !== '' ? $priceReference : null;

            $sortKey = $this->extractLowerBound($priceReference);
            $reason = $row['reason'] ?? null;

            $rows[] = [
                'name' => $name,
                'reason' => is_string($reason) && $reason !== '' ? $reason : null,
                'price_reference' => $priceReference,
                'sort_key' => $sortKey,
                'has_price' => $priceReference !== null,
            ];
        }

        usort($rows, function (array $a, array $b): int {
            if ($a['has_price'] !== $b['has_price']) {
                return $a['has_price'] ? -1 : 1;
            }

            if ($a['sort_key'] === $b['sort_key']) {
                return 0;
            }

            return $a['sort_key'] <=> $b['sort_key'];
        });

        return array_slice($rows, 0, 5);
    }

    private function extractLowerBound(?string $priceReference): float
    {
        if ($priceReference === null) {
            return PHP_FLOAT_MAX;
        }

        if (preg_match_all('/[0-9][0-9,\.]*/', $priceReference, $matches) && $matches[0] !== []) {
            $numbers = array_map(
                fn (string $raw): float => (float) str_replace(',', '', $raw),
                $matches[0],
            );

            $numbers = array_values(array_filter($numbers, fn (float $n): bool => $n > 0));

            if ($numbers !== []) {
                return (float) min($numbers);
            }
        }

        return PHP_FLOAT_MAX;
    }

    public function hasContent(): bool
    {
        return $this->priceReference() !== null || $this->alternatives() !== [];
    }

    #[Layout('components.layouts.app')]
    #[Title('Worthly · Offers')]
    public function render(): mixed
    {
        return view('livewire.result.offers-page', [
            'productName' => $this->productName(),
            'priceReference' => $this->priceReference(),
            'priceGuidance' => $this->priceGuidance(),
            'alternatives' => $this->alternatives(),
        ]);
    }
}
