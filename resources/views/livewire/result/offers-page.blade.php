<div
    data-testid="offers-page"
    style="background:var(--w-cream);min-height:100vh;padding-bottom:24px;"
>
    <x-ui.screen-header
        eyebrow="Offers & price"
        :title="$productName"
        :backHref="route('analyses.show', ['analysis' => $analysisId])"
    />

    @if ($priceReference)
        <div style="padding:4px 18px 14px;">
            <x-ui.card :padding="18" data-testid="offers-price-reference" style="background:var(--w-ink);color:#FAF8F2;border:0;">
                <div style="font-family:var(--font-mono);font-size:10px;font-weight:500;letter-spacing:0.14em;text-transform:uppercase;color:rgba(250,248,242,0.6);margin-bottom:8px;">
                    Price reference
                </div>
                <div
                    data-testid="offers-price-reference-headline"
                    style="font-family:var(--font-mono);font-size:32px;font-weight:500;color:#FAF8F2;letter-spacing:0.01em;margin-bottom:6px;"
                >{{ $priceReference }}</div>
                <div style="font-family:var(--font-mono);font-size:11px;letter-spacing:0.06em;color:rgba(250,248,242,0.6);text-transform:uppercase;">
                    Estimated by Worthly
                </div>
            </x-ui.card>
        </div>
    @endif

    @if ($priceGuidance)
        <div style="padding:6px 22px 4px;">
            <x-ui.section-label>Price guidance</x-ui.section-label>
        </div>
        <div style="padding:6px 18px 14px;">
            <x-ui.card :padding="16" data-testid="offers-guidance">
                <p style="font-family:var(--font-ui);font-size:13px;line-height:1.55;color:var(--w-ink-2);margin:0;">
                    {{ $priceGuidance }}
                </p>
            </x-ui.card>
        </div>
    @endif

    @if ($alternatives)
        <div style="padding:8px 22px 4px;">
            <x-ui.section-label>Alternatives by price</x-ui.section-label>
        </div>
        <div
            data-testid="offers-alternatives"
            style="padding:6px 18px 24px;display:flex;flex-direction:column;gap:8px;"
        >
            @foreach ($alternatives as $row)
                <x-ui.card :padding="14" data-testid="offers-alt-item" data-alt-name="{{ $row['name'] }}">
                    <div style="display:flex;align-items:center;gap:12px;">
                        <div style="flex:1;min-width:0;">
                            <div style="font-family:var(--font-ui);font-size:14px;font-weight:500;color:var(--w-ink);margin-bottom:2px;">
                                {{ $row['name'] }}
                            </div>
                            @if ($row['reason'])
                                <div style="font-family:var(--font-ui);font-size:12px;color:var(--w-muted);line-height:1.4;">
                                    {{ $row['reason'] }}
                                </div>
                            @endif
                        </div>
                        <div style="text-align:right;">
                            <div style="font-family:var(--font-mono);font-size:13px;font-weight:600;color:var(--w-ink);">
                                {{ $row['price_reference'] ?? '—' }}
                            </div>
                        </div>
                    </div>
                </x-ui.card>
            @endforeach
        </div>
    @endif
</div>
