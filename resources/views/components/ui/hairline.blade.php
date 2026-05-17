@props([
    'color' => 'var(--w-line)',
])

<div role="separator" aria-hidden="true" {{ $attributes->merge(['style' => 'height:1px;background:' . $color . ';width:100%;']) }}></div>
