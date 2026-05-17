@props([
    'padding' => 16,
    'radius' => 14,
    'as' => 'div',
])

@php
    $tag = in_array($as, ['div', 'section', 'article', 'aside', 'button', 'a'], true) ? $as : 'div';
    $cardStyle = sprintf(
        'background:var(--w-paper);border-radius:%dpx;border:0.5px solid var(--w-line);padding:%dpx;',
        (int) $radius,
        (int) $padding,
    );
@endphp

<{{ $tag }} {{ $attributes->merge(['style' => $cardStyle]) }}>
    {{ $slot }}
</{{ $tag }}>
