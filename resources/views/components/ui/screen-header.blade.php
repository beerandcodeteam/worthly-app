@props([
    'title' => null,
    'eyebrow' => null,
    'backHref' => null,
    'closeHref' => null,
    'sticky' => false,
    'transparent' => false,
])

@php
    $bg = $transparent ? 'transparent' : 'var(--w-cream)';
    $position = $sticky ? 'sticky' : 'relative';

    $iconBtnStyle = 'appearance:none;border:1px solid var(--w-line);background:transparent;width:32px;height:32px;border-radius:999px;display:inline-flex;align-items:center;justify-content:center;cursor:pointer;color:var(--w-ink);text-decoration:none;';
@endphp

<header {{ $attributes->merge([
    'style' => sprintf(
        'display:flex;align-items:center;justify-content:space-between;padding:62px 18px 14px;background:%s;position:%s;top:0;z-index:10;gap:12px;',
        $bg,
        $position,
    ),
]) }}>
    @if ($backHref)
        <a href="{{ $backHref }}" aria-label="Back" style="{{ $iconBtnStyle }}">
            <x-ui.icon name="chevron-left" :size="20" />
        </a>
    @elseif (isset($back))
        {{ $back }}
    @else
        <span style="width:32px;display:inline-block;"></span>
    @endif

    <div style="flex:1;text-align:center;overflow:hidden;min-width:0;">
        @if ($eyebrow)
            <div style="font-family:var(--font-mono);font-size:10px;font-weight:500;letter-spacing:0.12em;text-transform:uppercase;color:var(--w-muted);">
                {{ $eyebrow }}
            </div>
        @endif

        @if ($title)
            <div style="font-size:15px;font-weight:500;color:var(--w-ink);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                {{ $title }}
            </div>
        @endif
    </div>

    @if (isset($right))
        {{ $right }}
    @elseif ($closeHref)
        <a href="{{ $closeHref }}" aria-label="Close" style="{{ $iconBtnStyle }}">
            <x-ui.icon name="close" :size="18" />
        </a>
    @else
        <span style="width:32px;display:inline-block;"></span>
    @endif
</header>
