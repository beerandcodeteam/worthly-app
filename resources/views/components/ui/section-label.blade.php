@props([
    'label' => null,
])

<div {{ $attributes->merge([
    'style' => 'display:flex;align-items:baseline;justify-content:space-between;font-family:var(--font-mono);font-size:10px;font-weight:500;letter-spacing:0.14em;text-transform:uppercase;color:var(--w-muted);',
]) }}>
    <span>{{ $label ?? $slot }}</span>

    @isset($right)
        <span style="font-family:var(--font-mono);font-size:10px;color:var(--w-muted-2);letter-spacing:0.08em;">{{ $right }}</span>
    @endisset
</div>
