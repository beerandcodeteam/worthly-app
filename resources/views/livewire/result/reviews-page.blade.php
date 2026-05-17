<div
    data-testid="reviews-page"
    style="background:var(--w-cream);min-height:100vh;padding-bottom:24px;"
>
    <x-ui.screen-header
        eyebrow="Reviews & reputation"
        :title="$productName"
        :backHref="route('analyses.show', ['analysis' => $analysisId])"
    />

    @if ($summary)
        <div style="padding:4px 22px 6px;">
            <x-ui.section-label>Reputation summary</x-ui.section-label>
        </div>
        <div
            data-testid="reviews-reputation"
            style="padding:6px 18px 14px;"
        >
            <x-ui.card :padding="16">
                <p style="font-family:var(--font-ui);font-size:14px;line-height:1.55;color:var(--w-ink-2);margin:0;">
                    {{ $summary }}
                </p>
            </x-ui.card>
        </div>
    @endif

    @if ($costBenefitAnalysis)
        <div style="padding:8px 22px 6px;">
            <x-ui.section-label>What Worthly considered</x-ui.section-label>
        </div>
        <div
            data-testid="reviews-considered"
            style="padding:6px 18px 14px;"
        >
            <x-ui.card :padding="16">
                <p style="font-family:var(--font-ui);font-size:13px;line-height:1.55;color:var(--w-ink-2);margin:0;">
                    {{ $costBenefitAnalysis }}
                </p>
            </x-ui.card>
        </div>

        @if (! $prosCons['fallback'])
            @if ($prosCons['pros'])
                <div style="padding:8px 22px 6px;">
                    <x-ui.section-label>Top pros</x-ui.section-label>
                </div>
                <div
                    data-testid="reviews-top-pros"
                    style="padding:6px 18px 14px;display:flex;flex-direction:column;gap:8px;"
                >
                    @foreach ($prosCons['pros'] as $pro)
                        <x-ui.card :padding="14">
                            <div style="display:flex;gap:10px;align-items:flex-start;">
                                <span aria-hidden="true" style="margin-top:2px;width:18px;height:18px;border-radius:50%;background:var(--w-buy-soft);color:var(--w-buy);display:inline-flex;align-items:center;justify-content:center;flex-shrink:0;">
                                    <x-ui.icon name="check" :size="11" />
                                </span>
                                <span style="font-family:var(--font-ui);font-size:13px;line-height:1.5;color:var(--w-ink-2);">{{ $pro }}</span>
                            </div>
                        </x-ui.card>
                    @endforeach
                </div>
            @endif

            @if ($prosCons['cons'])
                <div style="padding:8px 22px 6px;">
                    <x-ui.section-label>Top cons</x-ui.section-label>
                </div>
                <div
                    data-testid="reviews-top-cons"
                    style="padding:6px 18px 24px;display:flex;flex-direction:column;gap:8px;"
                >
                    @foreach ($prosCons['cons'] as $con)
                        <x-ui.card :padding="14">
                            <div style="display:flex;gap:10px;align-items:flex-start;">
                                <span aria-hidden="true" style="margin-top:2px;width:18px;height:18px;border-radius:50%;background:var(--w-wait-soft);color:var(--w-wait);display:inline-flex;align-items:center;justify-content:center;flex-shrink:0;font-family:var(--font-mono);font-size:12px;font-weight:600;">!</span>
                                <span style="font-family:var(--font-ui);font-size:13px;line-height:1.5;color:var(--w-ink-2);">{{ $con }}</span>
                            </div>
                        </x-ui.card>
                    @endforeach
                </div>
            @endif
        @endif
    @endif
</div>
