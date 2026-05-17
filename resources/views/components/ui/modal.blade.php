@props([
    'open' => false,
    'title' => null,
    'eyebrow' => null,
    'closeHref' => null,
    'id' => null,
    'maxWidth' => 480,
])

@php
    $modalId = $id ?? 'modal-' . uniqid();
    $maxWidthPx = (int) $maxWidth;
@endphp

<div
    id="{{ $modalId }}"
    role="dialog"
    aria-modal="true"
    @if ($title) aria-label="{{ $title }}" @endif
    {{ $attributes->merge([
        'style' => sprintf(
            'position:fixed;inset:0;display:%s;align-items:flex-end;justify-content:center;background:rgba(20,19,15,0.45);z-index:60;padding:24px 0 0;',
            $open ? 'flex' : 'none',
        ),
    ]) }}
>
    <div style="background:var(--w-cream);width:100%;max-width:{{ $maxWidthPx }}px;border-radius:22px 22px 0 0;box-shadow:0 -12px 40px rgba(20,19,15,0.18);display:flex;flex-direction:column;max-height:90vh;overflow:hidden;">
        @if ($title || $eyebrow || isset($header) || $closeHref)
            <div style="padding:18px 18px 12px;display:flex;align-items:center;justify-content:space-between;gap:12px;border-bottom:0.5px solid var(--w-line);">
                <div style="flex:1;min-width:0;">
                    @if ($eyebrow)
                        <div style="font-family:var(--font-mono);font-size:10px;font-weight:500;letter-spacing:0.12em;text-transform:uppercase;color:var(--w-muted);margin-bottom:4px;">
                            {{ $eyebrow }}
                        </div>
                    @endif

                    @if (isset($header))
                        {{ $header }}
                    @elseif ($title)
                        <div style="font-family:var(--font-display);font-style:italic;font-size:24px;line-height:1.1;color:var(--w-ink);">
                            {{ $title }}
                        </div>
                    @endif
                </div>

                @if ($closeHref)
                    <a href="{{ $closeHref }}" aria-label="Close" style="appearance:none;border:1px solid var(--w-line);background:transparent;width:32px;height:32px;border-radius:999px;display:inline-flex;align-items:center;justify-content:center;cursor:pointer;color:var(--w-ink);text-decoration:none;">
                        <x-ui.icon name="close" :size="18" />
                    </a>
                @endif
            </div>
        @endif

        <div style="flex:1;min-height:0;overflow-y:auto;padding:18px;">
            {{ $slot }}
        </div>

        @isset($footer)
            <div style="padding:14px 18px 22px;border-top:0.5px solid var(--w-line);display:flex;flex-direction:column;gap:10px;">
                {{ $footer }}
            </div>
        @endisset
    </div>
</div>
