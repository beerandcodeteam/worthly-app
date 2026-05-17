<div
    data-testid="result-page"
    wire:init="loadImage"
    style="display:flex;flex-direction:column;flex:1;padding:0 0 24px;background:var(--w-cream);min-height:100vh;"
>
    {{-- Header --}}
    <div style="display:flex;align-items:center;justify-content:space-between;padding:60px 18px 14px;gap:12px;">
        <a
            href="{{ route('home') }}"
            wire:navigate
            aria-label="Back"
            style="appearance:none;border:0.5px solid var(--w-line);background:transparent;width:32px;height:32px;border-radius:999px;display:inline-flex;align-items:center;justify-content:center;color:var(--w-ink);text-decoration:none;"
        >
            <x-ui.icon name="chevron-left" :size="20" />
        </a>
        <span style="font-family:var(--font-mono);font-size:10px;color:var(--w-muted);letter-spacing:0.12em;text-transform:uppercase;">
            Analysis
        </span>
        <span style="width:32px;display:inline-block;"></span>
    </div>

    {{-- Hero verdict --}}
    <div
        data-testid="result-hero"
        data-order="0"
        style="padding:8px 18px 20px;"
    >
        @php
            $heroSoft = match (optional($verdict)->value) {
                'buy' => 'var(--w-buy-soft)',
                'wait' => 'var(--w-wait-soft)',
                'skip' => 'var(--w-skip-soft)',
                default => 'var(--w-cream-2)',
            };
            $heroColor = $verdict?->color() ?? 'var(--w-ink)';
        @endphp

        <div style="position:relative;overflow:hidden;background:var(--w-paper);border:0.5px solid var(--w-line);border-radius:18px;padding:18px;">
            <div aria-hidden="true" style="position:absolute;top:-40px;right:-40px;width:160px;height:160px;border-radius:50%;background:{{ $heroSoft }};opacity:0.85;"></div>

            <div style="position:relative;">
                @if ($shouldLoadImage)
                    <div
                        data-testid="hero-image-card"
                        style="margin-bottom:14px;"
                    >
                        @if ($imageLoaded && $imageDataUrl)
                            <img
                                data-testid="hero-image"
                                src="{{ $imageDataUrl }}"
                                alt="{{ $productName }}"
                                style="display:block;width:100%;aspect-ratio:1 / 1;object-fit:cover;border-radius:12px;border:0.5px solid var(--w-line);background:var(--w-cream-2);"
                            />
                        @elseif ($imageMissing)
                            <div
                                data-testid="hero-image-unavailable"
                                role="img"
                                aria-label="Image unavailable"
                                style="display:flex;flex-direction:column;align-items:center;justify-content:center;gap:8px;width:100%;aspect-ratio:1 / 1;border-radius:12px;border:0.5px dashed var(--w-line-2);background:var(--w-cream-2);color:var(--w-muted);"
                            >
                                <x-ui.icon name="image" :size="22" color="var(--w-muted-2)" />
                                <span style="font-family:var(--font-mono);font-size:11px;letter-spacing:0.06em;text-transform:uppercase;color:var(--w-muted);">Image unavailable</span>
                            </div>
                        @else
                            <div
                                data-testid="hero-image-skeleton"
                                aria-hidden="true"
                                style="display:block;width:100%;aspect-ratio:1 / 1;border-radius:12px;border:0.5px solid var(--w-line);background:linear-gradient(135deg,var(--w-cream-2) 0%,var(--w-paper) 50%,var(--w-cream-2) 100%);"
                            ></div>
                        @endif
                    </div>
                @endif

                <div style="display:flex;flex-direction:column;gap:6px;margin-bottom:14px;">
                    @if ($productCategory)
                        <div
                            data-testid="hero-category"
                            style="font-family:var(--font-mono);font-size:10px;font-weight:500;letter-spacing:0.12em;text-transform:uppercase;color:var(--w-muted);"
                        >{{ $productCategory }}</div>
                    @endif

                    <div
                        data-testid="hero-product-name"
                        style="font-size:18px;font-weight:600;line-height:1.2;color:var(--w-ink);"
                    >{{ $productName }}</div>

                    @if ($estimatedPriceRange)
                        <div
                            data-testid="hero-price-range"
                            style="font-family:var(--font-mono);font-size:13px;color:var(--w-ink-2);letter-spacing:0.02em;"
                        >{{ $estimatedPriceRange }}</div>
                    @endif
                </div>

                <div style="display:flex;align-items:center;gap:14px;padding:14px 0;border-top:0.5px solid var(--w-line);border-bottom:0.5px solid var(--w-line);margin-bottom:14px;">
                    @if ($verdict)
                        <x-ui.verdict-pill :verdict="$verdict->value" size="lg" data-testid="hero-verdict-pill" />
                    @endif

                    <div style="flex:1;min-width:0;">
                        @if ($verdict)
                            <div style="font-family:var(--font-mono);font-size:10px;font-weight:500;letter-spacing:0.12em;text-transform:uppercase;color:{{ $heroColor }};margin-bottom:4px;">Verdict</div>
                        @endif

                        @if ($tldr !== '')
                            <p
                                data-testid="hero-tldr"
                                style="font-family:var(--font-display);font-style:italic;font-size:20px;line-height:1.15;color:var(--w-ink);margin:0;"
                            >{{ $tldr }}</p>
                        @endif
                    </div>
                </div>

                @if ($isPriceConditional)
                    <p
                        data-testid="hero-price-conditional"
                        style="margin:0;padding:10px 12px;border-radius:10px;background:var(--w-cream-2);font-family:var(--font-ui);font-size:13px;line-height:1.45;color:var(--w-ink-2);"
                    >
                        Worth it only if the current price beats the typical range. Check today's price before buying.
                    </p>
                @endif
            </div>
        </div>
    </div>

    {{-- Advisor summary --}}
    @if ($summary)
        <div
            data-testid="result-summary"
            data-order="1"
            style="padding:0 18px 14px;"
        >
            <div style="padding:0 4px 8px;">
                <x-ui.section-label>Advisor summary</x-ui.section-label>
            </div>
            <x-ui.card :padding="16">
                <p style="font-family:var(--font-ui);font-size:14px;line-height:1.55;color:var(--w-ink-2);margin:0;">
                    {{ $summary }}
                </p>
            </x-ui.card>
        </div>
    @endif

    {{-- Why card --}}
    @if ($costBenefitAnalysis)
        <div
            data-testid="result-why"
            data-order="2"
            style="padding:0 18px 14px;"
        >
            <div style="padding:0 4px 8px;">
                <x-ui.section-label>Why</x-ui.section-label>
            </div>
            <x-ui.card :padding="16">
                @if ($prosCons['fallback'])
                    <p style="font-family:var(--font-ui);font-size:13px;line-height:1.55;color:var(--w-ink-2);margin:0;">
                        {{ $prosCons['fallback'] }}
                    </p>
                @else
                    <div style="display:flex;flex-direction:column;gap:14px;">
                        @if ($prosCons['pros'])
                            <div data-testid="why-pros">
                                <div style="font-family:var(--font-mono);font-size:10px;font-weight:500;letter-spacing:0.12em;text-transform:uppercase;color:var(--w-buy);margin-bottom:8px;">
                                    Reasons for
                                </div>
                                <ul style="list-style:none;margin:0;padding:0;display:flex;flex-direction:column;gap:8px;">
                                    @foreach ($prosCons['pros'] as $pro)
                                        <li style="display:flex;gap:10px;align-items:flex-start;">
                                            <span aria-hidden="true" style="margin-top:2px;width:16px;height:16px;border-radius:50%;background:var(--w-buy-soft);color:var(--w-buy);display:inline-flex;align-items:center;justify-content:center;flex-shrink:0;">
                                                <x-ui.icon name="check" :size="10" />
                                            </span>
                                            <span style="font-family:var(--font-ui);font-size:13px;line-height:1.5;color:var(--w-ink-2);">{{ $pro }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if ($prosCons['cons'])
                            <div data-testid="why-cons">
                                <div style="font-family:var(--font-mono);font-size:10px;font-weight:500;letter-spacing:0.12em;text-transform:uppercase;color:var(--w-wait);margin-bottom:8px;">
                                    Reasons against
                                </div>
                                <ul style="list-style:none;margin:0;padding:0;display:flex;flex-direction:column;gap:8px;">
                                    @foreach ($prosCons['cons'] as $con)
                                        <li style="display:flex;gap:10px;align-items:flex-start;">
                                            <span aria-hidden="true" style="margin-top:2px;width:16px;height:16px;border-radius:50%;background:var(--w-wait-soft);color:var(--w-wait);display:inline-flex;align-items:center;justify-content:center;flex-shrink:0;font-family:var(--font-mono);font-size:11px;font-weight:600;">!</span>
                                            <span style="font-family:var(--font-ui);font-size:13px;line-height:1.5;color:var(--w-ink-2);">{{ $con }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                @endif
            </x-ui.card>
        </div>
    @endif

    {{-- Price right now --}}
    @if ($estimatedPriceRange)
        <div
            data-testid="price-card"
            data-order="3"
            style="padding:0 18px 14px;"
        >
            <x-ui.card :padding="16">
                <div style="display:flex;justify-content:space-between;align-items:baseline;margin-bottom:10px;">
                    <x-ui.section-label>Price right now</x-ui.section-label>
                </div>
                <div
                    data-testid="price-headline"
                    style="font-family:var(--font-mono);font-size:22px;font-weight:600;color:var(--w-ink);letter-spacing:0.01em;margin-bottom:6px;"
                >{{ $estimatedPriceRange }}</div>
                <div
                    data-testid="price-band"
                    aria-hidden="true"
                    style="height:6px;border-radius:4px;background:linear-gradient(90deg,var(--w-buy-soft) 0%,var(--w-paper) 50%,var(--w-wait-soft) 100%);border:0.5px solid var(--w-line);margin:8px 0 8px;"
                ></div>
                <div
                    data-testid="price-caption"
                    style="font-family:var(--font-mono);font-size:10px;letter-spacing:0.06em;text-transform:uppercase;color:var(--w-muted);"
                >Estimated by Worthly</div>
            </x-ui.card>
        </div>
    @endif

    {{-- Drill-ins --}}
    <div
        data-testid="result-drillins"
        data-order="4"
        style="padding:8px 18px 18px;"
    >
        <x-ui.card :padding="0" style="overflow:hidden;">
            @if ($hasSimilar)
                <a
                    href="{{ route('analyses.similar', ['analysis' => $analysisId]) }}"
                    wire:navigate
                    data-testid="drillin-similar"
                    style="appearance:none;text-decoration:none;display:flex;align-items:center;justify-content:space-between;padding:16px;color:var(--w-ink);"
                >
                    <span style="font-family:var(--font-ui);font-size:14px;font-weight:500;color:var(--w-ink);">Similar products</span>
                    <span style="display:flex;align-items:center;gap:10px;">
                        <span style="font-family:var(--font-mono);font-size:11px;color:var(--w-muted);letter-spacing:0.02em;">
                            {{ $similarCount === 1 ? '1 alternative' : $similarCount.' alternatives' }}
                        </span>
                        <x-ui.icon name="chevron-right" :size="14" color="var(--w-muted-2)" />
                    </span>
                </a>
            @endif

            @if ($hasSimilar && $hasReviewsContent)
                <x-ui.hairline />
            @endif

            @if ($hasReviewsContent)
                <a
                    href="{{ route('analyses.reviews', ['analysis' => $analysisId]) }}"
                    wire:navigate
                    data-testid="drillin-reviews"
                    style="appearance:none;text-decoration:none;display:flex;align-items:center;justify-content:space-between;padding:16px;color:var(--w-ink);"
                >
                    <span style="font-family:var(--font-ui);font-size:14px;font-weight:500;color:var(--w-ink);">Reviews &amp; reputation</span>
                    <span style="display:flex;align-items:center;gap:10px;">
                        <span style="font-family:var(--font-mono);font-size:11px;color:var(--w-muted);letter-spacing:0.02em;">Summary</span>
                        <x-ui.icon name="chevron-right" :size="14" color="var(--w-muted-2)" />
                    </span>
                </a>
            @endif

            @if (($hasSimilar || $hasReviewsContent) && $hasOffersContent)
                <x-ui.hairline />
            @endif

            @if ($hasOffersContent)
                <a
                    href="{{ route('analyses.offers', ['analysis' => $analysisId]) }}"
                    wire:navigate
                    data-testid="drillin-offers"
                    style="appearance:none;text-decoration:none;display:flex;align-items:center;justify-content:space-between;padding:16px;color:var(--w-ink);"
                >
                    <span style="font-family:var(--font-ui);font-size:14px;font-weight:500;color:var(--w-ink);">Offers &amp; price history</span>
                    <span style="display:flex;align-items:center;gap:10px;">
                        <span style="font-family:var(--font-mono);font-size:11px;color:var(--w-muted);letter-spacing:0.02em;">Price reference</span>
                        <x-ui.icon name="chevron-right" :size="14" color="var(--w-muted-2)" />
                    </span>
                </a>
            @endif
        </x-ui.card>
    </div>

    {{-- Bottom CTAs --}}
    <div
        data-testid="result-bottom-ctas"
        data-order="5"
        style="padding:0 18px 24px;display:flex;gap:8px;"
    >
        <button
            type="button"
            wire:click="newAnalysis"
            data-testid="cta-new-analysis"
            style="appearance:none;border:1px solid var(--w-line-2);background:var(--w-paper);color:var(--w-ink);width:100%;height:48px;padding:0 16px;border-radius:14px;font-family:var(--font-ui);font-size:14px;font-weight:500;display:inline-flex;align-items:center;justify-content:center;gap:8px;cursor:pointer;"
        >
            <x-ui.icon name="plus" :size="16" />
            New analysis
        </button>
        <button
            type="button"
            wire:click="seeBestOffer"
            data-testid="cta-see-best-offer"
            style="appearance:none;border:0;background:var(--w-ink);color:#FAF8F2;width:100%;height:48px;padding:0 16px;border-radius:14px;font-family:var(--font-ui);font-size:14px;font-weight:500;display:inline-flex;align-items:center;justify-content:center;gap:8px;cursor:pointer;"
        >
            See best offer
            <x-ui.icon name="arrow-right" :size="16" />
        </button>
    </div>
</div>
