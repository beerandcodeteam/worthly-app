@props([
    'label' => null,
    'name' => null,
    'id' => null,
    'type' => 'text',
    'value' => null,
    'placeholder' => null,
    'hint' => null,
    'error' => null,
    'leading' => null,
    'trailing' => null,
    'disabled' => false,
    'required' => false,
])

@php
    $fieldId = $id ?? ($name ? 'input-' . $name : 'input-' . uniqid());
    $hasError = ! empty($error);
    $borderColor = $hasError ? 'var(--w-skip)' : 'var(--w-line-2)';
    $isPassword = $type === 'password';
@endphp

<div {{ $attributes->only('class')->merge(['class' => 'w-full']) }}>
    <label
        for="{{ $fieldId }}"
        style="display:block;position:relative;border:1px solid {{ $borderColor }};border-radius:12px;padding:10px 14px;background:var(--w-paper);transition:border-color 120ms;"
    >
        @if ($label)
            <div style="font-family:var(--font-mono);font-size:10px;font-weight:500;letter-spacing:0.1em;text-transform:uppercase;color:var(--w-muted);margin-bottom:2px;">
                {{ $label }}@if ($required)<span style="color:var(--w-skip);"> *</span>@endif
            </div>
        @endif

        <div style="display:flex;align-items:center;gap:8px;">
            @if ($leading)
                <span style="display:inline-flex;align-items:center;color:var(--w-muted);">{{ $leading }}</span>
            @endif

            <input
                id="{{ $fieldId }}"
                @if ($name) name="{{ $name }}" @endif
                type="{{ $type }}"
                @if ($value !== null) value="{{ $value }}" @endif
                @if ($placeholder) placeholder="{{ $placeholder }}" @endif
                @disabled($disabled)
                @required($required)
                {{ $attributes->except('class')->merge([
                    'style' => sprintf(
                        'width:100%%;border:0;outline:0;background:transparent;font-family:%s;font-size:15px;color:var(--w-ink);padding:0;',
                        $isPassword ? 'var(--font-mono)' : 'var(--font-ui)',
                    ),
                ]) }}
            />

            @if ($trailing)
                <span style="display:inline-flex;align-items:center;color:var(--w-muted);">{{ $trailing }}</span>
            @endif
        </div>
    </label>

    @if ($hasError)
        <div style="margin-top:6px;font-family:var(--font-ui);font-size:12px;color:var(--w-skip);">
            {{ $error }}
        </div>
    @elseif ($hint)
        <div style="margin-top:6px;font-family:var(--font-ui);font-size:12px;color:var(--w-muted);">
            {{ $hint }}
        </div>
    @endif
</div>
