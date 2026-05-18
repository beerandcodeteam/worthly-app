<div
    x-data="{
        startX: null,
        threshold: 40,
        onTouchStart(e) {
            this.startX = e.touches[0].clientX;
        },
        onTouchEnd(e) {
            if (this.startX === null) return;
            const delta = e.changedTouches[0].clientX - this.startX;
            this.startX = null;
            if (Math.abs(delta) < this.threshold) return;
            delta < 0 ? $wire.next() : $wire.previous();
        },
    }"
    x-on:touchstart="onTouchStart"
    x-on:touchend="onTouchEnd"
    style="display:flex;flex-direction:column;flex:1;padding:70px 28px 36px;background:var(--w-cream);min-height:100vh;"
>
    <div style="display:flex;justify-content:space-between;align-items:center;">
        <span style="font-family:var(--font-display);font-style:italic;font-size:22px;line-height:1;letter-spacing:-0.01em;color:var(--w-ink);display:inline-flex;align-items:baseline;gap:1px;">
            Worthly<span aria-hidden="true" style="width:5px;height:5px;border-radius:50%;background:var(--w-buy);display:inline-block;margin-bottom:2.6px;"></span>
        </span>
        <button
            type="button"
            wire:click="skip"
            data-testid="onboarding-skip"
            style="appearance:none;background:transparent;border:0;cursor:pointer;font-family:var(--font-mono);font-size:11px;color:var(--w-muted);letter-spacing:0.08em;text-transform:uppercase;"
        >Skip</button>
    </div>

    <div style="flex:1;display:flex;flex-direction:column;justify-content:center;align-items:center;text-align:center;padding:0 4px;gap:28px;">
        @php($slide = $slides[$currentSlide])

        <div wire:key="art-{{ $currentSlide }}" data-testid="onboarding-art-{{ $slide['art'] }}" class="onboarding-art-enter">
            @include('livewire.onboarding._art', ['kind' => $slide['art']])
        </div>

        <div wire:key="copy-{{ $currentSlide }}" data-testid="onboarding-slide" class="onboarding-copy-enter">
            <div style="font-family:var(--font-mono);font-size:11px;font-weight:500;letter-spacing:0.14em;text-transform:uppercase;color:var(--w-muted);margin-bottom:16px;">
                {{ $slide['eyebrow'] }}
            </div>
            <h1 style="font-family:var(--font-display);font-weight:400;font-size:40px;line-height:1.05;letter-spacing:-0.01em;color:var(--w-ink);margin:0 0 16px;">
                {!! $slide['headline_html'] !!}
            </h1>
            <p style="font-size:15px;line-height:1.5;color:var(--w-muted);margin:0 auto;max-width:300px;" data-testid="onboarding-body">
                {{ $slide['body'] }}
            </p>
        </div>
    </div>

    <div style="display:flex;justify-content:center;gap:6px;margin-bottom:18px;">
        @foreach ($slides as $index => $_)
            <button
                type="button"
                wire:click="goTo({{ $index }})"
                data-testid="onboarding-dot-{{ $index }}"
                aria-label="Go to slide {{ $index + 1 }}"
                @style([
                    'border:0;cursor:pointer;height:6px;border-radius:999px;transition:width 200ms ease, background-color 200ms ease;padding:0;',
                    'width:22px;background:var(--w-ink);' => $index === $currentSlide,
                    'width:6px;background:var(--w-line-2);' => $index !== $currentSlide,
                ])
            ></button>
        @endforeach
    </div>

    @if ($this->isLastSlide)
        <div style="display:flex;flex-direction:column;gap:10px;">
            <x-ui.button wire:click="getStarted" variant="ink" data-testid="onboarding-get-started">
                Get started
                <x-ui.icon name="arrow-right" :size="16" />
            </x-ui.button>
            <button
                type="button"
                wire:click="iHaveAnAccount"
                data-testid="onboarding-have-account"
                style="appearance:none;background:transparent;border:0;margin-top:6px;padding:10px;color:var(--w-muted);font-family:var(--font-ui);font-size:13px;cursor:pointer;"
            >I already have an account</button>
        </div>
    @else
        <x-ui.button wire:click="next" variant="ink" data-testid="onboarding-continue">
            Continue
            <x-ui.icon name="arrow-right" :size="16" />
        </x-ui.button>
    @endif

    @once
        <style>
            @keyframes worthly-onboarding-scan {
                0%, 100% { transform: translateY(-60px); }
                50%      { transform: translateY(60px); }
            }
            @keyframes worthly-onboarding-fade-up {
                from { opacity: 0; transform: translateY(8px); }
                to   { opacity: 1; transform: translateY(0); }
            }
            @keyframes worthly-onboarding-pop {
                0%   { opacity: 0; transform: translateY(10px) scale(0.96); }
                100% { opacity: 1; transform: translateY(0) scale(1); }
            }
            .onboarding-art-enter  { animation: worthly-onboarding-pop 360ms cubic-bezier(0.2, 0.7, 0.2, 1) both; }
            .onboarding-copy-enter { animation: worthly-onboarding-fade-up 320ms cubic-bezier(0.2, 0.7, 0.2, 1) both; animation-delay: 60ms; }
            .onboarding-scan-line  { animation: worthly-onboarding-scan 2.4s ease-in-out infinite; }
            @media (prefers-reduced-motion: reduce) {
                .onboarding-art-enter,
                .onboarding-copy-enter,
                .onboarding-scan-line { animation: none !important; }
            }
        </style>
    @endonce
</div>
