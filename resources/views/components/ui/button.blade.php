@props([
    'variant' => 'ink',
    'size' => 'md',
    'type' => 'button',
    'disabled' => false,
    'full' => true,
    'href' => null,
])

@php
    $heights = [
        'sm' => 40,
        'md' => 52,
        'lg' => 56,
    ];
    $paddings = [
        'sm' => '0 16px',
        'md' => '0 22px',
        'lg' => '0 26px',
    ];
    $fontSizes = [
        'sm' => 13,
        'md' => 15,
        'lg' => 16,
    ];

    $height = $heights[$size] ?? $heights['md'];
    $paddingX = $paddings[$size] ?? $paddings['md'];
    $fontSize = $fontSizes[$size] ?? $fontSizes['md'];

    $bg = match (true) {
        $disabled => 'rgba(20,19,15,0.4)',
        $variant === 'paper' => 'var(--w-paper)',
        $variant === 'buy' => 'var(--w-buy)',
        default => 'var(--w-ink)',
    };
    $color = $variant === 'paper' ? 'var(--w-ink)' : '#FAF8F2';
    $border = $variant === 'paper' ? '1px solid var(--w-line-2)' : '0';

    $style = sprintf(
        'appearance:none;border:%s;background:%s;color:%s;width:%s;height:%dpx;padding:%s;border-radius:14px;font-family:var(--font-ui);font-size:%dpx;font-weight:500;cursor:%s;display:inline-flex;align-items:center;justify-content:center;gap:8px;letter-spacing:-0.005em;text-decoration:none;',
        $border,
        $bg,
        $color,
        $full ? '100%' : 'auto',
        $height,
        $paddingX,
        $fontSize,
        $disabled ? 'default' : 'pointer',
    );
@endphp

@if ($href && ! $disabled)
    <a
        href="{{ $href }}"
        {{ $attributes->merge(['style' => $style]) }}
    >{{ $slot }}</a>
@else
    <button
        type="{{ $type }}"
        @disabled($disabled)
        {{ $attributes->merge(['style' => $style]) }}
    >{{ $slot }}</button>
@endif
