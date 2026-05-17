@props([
    'verdict',
    'size' => 'md',
])

@php
    $verdictKey = strtolower((string) $verdict);

    $tokens = [
        'buy' => [
            'color' => 'var(--w-buy)',
            'soft' => 'var(--w-buy-soft)',
            'dot' => 'var(--w-buy)',
            'code' => 'BUY',
        ],
        'wait' => [
            'color' => 'var(--w-wait)',
            'soft' => 'var(--w-wait-soft)',
            'dot' => 'var(--w-wait)',
            'code' => 'WAIT',
        ],
        'skip' => [
            'color' => 'var(--w-skip)',
            'soft' => 'var(--w-skip-soft)',
            'dot' => 'var(--w-skip)',
            'code' => 'SKIP',
        ],
    ];

    $token = $tokens[$verdictKey] ?? null;

    $dimsBySize = [
        'sm' => ['fs' => 10, 'py' => 3, 'px' => 7, 'gap' => 5, 'dot' => 5],
        'md' => ['fs' => 11, 'py' => 4, 'px' => 9, 'gap' => 6, 'dot' => 6],
        'lg' => ['fs' => 13, 'py' => 7, 'px' => 12, 'gap' => 8, 'dot' => 8],
    ];
    $dims = $dimsBySize[$size] ?? $dimsBySize['md'];
@endphp

@if ($token)
    <span {{ $attributes->merge([
        'style' => sprintf(
            'display:inline-flex;align-items:center;gap:%dpx;padding:%dpx %dpx;background:%s;color:%s;border-radius:999px;font-family:var(--font-mono);font-size:%dpx;font-weight:600;letter-spacing:0.08em;text-transform:uppercase;',
            $dims['gap'],
            $dims['py'],
            $dims['px'],
            $token['soft'],
            $token['color'],
            $dims['fs'],
        ),
    ]) }}>
        <span aria-hidden="true" style="width:{{ $dims['dot'] }}px;height:{{ $dims['dot'] }}px;border-radius:50%;background:{{ $token['dot'] }};"></span>
        {{ $token['code'] }}
    </span>
@endif
