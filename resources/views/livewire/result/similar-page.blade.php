<div
    data-testid="similar-page"
    style="background:var(--w-cream);min-height:100vh;padding-bottom:24px;"
>
    <x-ui.screen-header
        eyebrow="Similar to"
        :title="$productName"
        :backHref="route('analyses.show', ['analysis' => $analysisId])"
    />

    <div style="padding:4px 22px 14px;">
        <p style="font-family:var(--font-ui);font-size:13px;line-height:1.5;color:var(--w-muted);margin:0 0 14px;">
            Alternatives Worthly considered alongside your product.
        </p>
        <x-ui.section-label>Alternatives</x-ui.section-label>
    </div>

    <div
        data-testid="similar-list"
        style="padding:0 18px 14px;display:flex;flex-direction:column;gap:10px;"
    >
        @foreach ($rows as $row)
            <x-ui.card :padding="16" data-testid="similar-item" data-similar-name="{{ $row['name'] }}">
                <div style="display:flex;align-items:flex-start;gap:12px;">
                    <x-ui.product-image :brand="$row['name']" :size="56" :radius="10" />
                    <div style="flex:1;min-width:0;">
                        <div
                            data-testid="similar-name"
                            style="font-family:var(--font-ui);font-size:15px;font-weight:600;color:var(--w-ink);margin-bottom:4px;"
                        >{{ $row['name'] }}</div>
                        <div
                            data-testid="similar-price"
                            style="font-family:var(--font-mono);font-size:12px;color:var(--w-ink-2);letter-spacing:0.02em;"
                        >{{ $row['price_reference'] ?? '—' }}</div>
                    </div>
                </div>
                @if ($row['reason'])
                    <p
                        data-testid="similar-reason"
                        style="font-family:var(--font-ui);font-size:13px;line-height:1.5;color:var(--w-ink-2);margin:12px 0 0;padding-top:12px;border-top:0.5px solid var(--w-line);"
                    >{{ $row['reason'] }}</p>
                @endif
            </x-ui.card>
        @endforeach
    </div>

    @if ($firstSimilar)
        <div style="padding:14px 22px 8px;">
            <x-ui.section-label>At a glance</x-ui.section-label>
        </div>
        <div style="padding:0 18px 24px;">
            <x-ui.card :padding="0" style="overflow:hidden;">
                <div
                    data-testid="similar-compare"
                    style="display:grid;grid-template-columns:1fr 1fr;gap:0;"
                >
                    <div style="padding:14px;border-right:0.5px solid var(--w-line);">
                        <div style="font-family:var(--font-mono);font-size:9px;font-weight:500;letter-spacing:0.12em;text-transform:uppercase;color:var(--w-muted);margin-bottom:6px;">This</div>
                        <div style="font-family:var(--font-ui);font-size:14px;font-weight:500;color:var(--w-ink);margin-bottom:4px;">{{ $productName }}</div>
                        <div style="font-family:var(--font-mono);font-size:12px;color:var(--w-ink-2);">{{ $productPriceRange ?? '—' }}</div>
                    </div>
                    <div style="padding:14px;">
                        <div style="font-family:var(--font-mono);font-size:9px;font-weight:500;letter-spacing:0.12em;text-transform:uppercase;color:var(--w-muted);margin-bottom:6px;">Alternative</div>
                        <div style="font-family:var(--font-ui);font-size:14px;font-weight:500;color:var(--w-ink);margin-bottom:4px;">{{ $firstSimilar['name'] }}</div>
                        <div style="font-family:var(--font-mono);font-size:12px;color:var(--w-ink-2);">{{ $firstSimilar['price_reference'] ?? '—' }}</div>
                    </div>
                </div>
            </x-ui.card>
        </div>
    @endif
</div>
