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
        <span style="font-family:var(--font-display);font-size:18px;color:var(--w-ink);">Worthly</span>
        <button
            type="button"
            wire:click="skip"
            data-testid="onboarding-skip"
            style="appearance:none;background:transparent;border:0;cursor:pointer;font-family:var(--font-mono);font-size:11px;color:var(--w-muted);letter-spacing:0.08em;text-transform:uppercase;"
        >Skip</button>
    </div>

    <div style="flex:1;display:flex;flex-direction:column;justify-content:center;align-items:center;text-align:center;padding:0 4px;gap:28px;">
        @php($slide = $slides[$currentSlide])

        <div data-testid="onboarding-slide">
            <div style="font-family:var(--font-mono);font-size:11px;font-weight:500;letter-spacing:0.14em;text-transform:uppercase;color:var(--w-muted);margin-bottom:16px;">
                {{ $slide['eyebrow'] }}
            </div>
            <h1 style="font-family:var(--font-display);font-weight:400;font-size:40px;line-height:1.05;letter-spacing:-0.01em;color:var(--w-ink);margin:0 0 16px;">
                {{ $slide['headline'] }}
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
                @style([
                    'border:0;cursor:pointer;height:6px;border-radius:999px;transition:width 200ms;',
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
        </x-ui.button>
    @endif
</div>
