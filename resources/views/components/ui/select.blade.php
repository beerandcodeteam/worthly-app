@props([
    'label' => null,
    'name' => null,
    'id' => null,
    'value' => null,
    'options' => [],
    'placeholder' => null,
    'hint' => null,
    'error' => null,
    'disabled' => false,
    'required' => false,
])

@php
    $fieldId = $id ?? ($name ? 'select-' . $name : 'select-' . uniqid());
    $hasError = ! empty($error);
    $borderColor = $hasError ? 'var(--w-skip)' : 'var(--w-line-2)';
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
            <select
                id="{{ $fieldId }}"
                @if ($name) name="{{ $name }}" @endif
                @disabled($disabled)
                @required($required)
                {{ $attributes->except('class')->merge([
                    'style' => 'width:100%;border:0;outline:0;background:transparent;appearance:none;-webkit-appearance:none;-moz-appearance:none;font-family:var(--font-ui);font-size:15px;color:var(--w-ink);padding:0 24px 0 0;cursor:pointer;',
                ]) }}
            >
                @if ($placeholder)
                    <option value="" disabled @selected($value === null || $value === '')>{{ $placeholder }}</option>
                @endif

                @if (! empty($options))
                    @foreach ($options as $optionValue => $optionLabel)
                        <option value="{{ $optionValue }}" @selected((string) $value === (string) $optionValue)>{{ $optionLabel }}</option>
                    @endforeach
                @else
                    {{ $slot }}
                @endif
            </select>

            <span style="pointer-events:none;display:inline-flex;align-items:center;color:var(--w-muted);position:absolute;right:12px;top:50%;transform:translateY(-50%);">
                <x-ui.icon name="chevron-down" :size="16" />
            </span>
        </div>
    </label>

    @if ($hasError)
        <div style="margin-top:6px;font-family:var(--font-ui);font-size:12px;color:var(--w-skip);">{{ $error }}</div>
    @elseif ($hint)
        <div style="margin-top:6px;font-family:var(--font-ui);font-size:12px;color:var(--w-muted);">{{ $hint }}</div>
    @endif
</div>
