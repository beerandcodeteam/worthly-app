@props([
    'size' => 22,
    'color' => 'var(--w-ink)',
])

@php
    $size = (int) $size;
    $dotMarginBottom = round($size * 0.12, 2);
@endphp

<span {{ $attributes->merge([
    'style' => sprintf(
        'font-family:var(--font-display);font-style:italic;font-weight:400;font-size:%dpx;line-height:1;letter-spacing:-0.01em;color:%s;display:inline-flex;align-items:baseline;gap:1px;',
        $size,
        $color,
    ),
]) }}>
    Worthly<span aria-hidden="true" style="width:5px;height:5px;border-radius:50%;background:var(--w-buy);display:inline-block;margin-bottom:{{ $dotMarginBottom }}px;"></span>
</span>
