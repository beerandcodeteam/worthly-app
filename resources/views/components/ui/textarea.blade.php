@props([
    'label' => null,
    'name' => null,
    'id' => null,
    'value' => null,
    'placeholder' => null,
    'hint' => null,
    'error' => null,
    'rows' => 3,
    'maxlength' => null,
    'disabled' => false,
    'required' => false,
])

@php
    $fieldId = $id ?? ($name ? 'textarea-' . $name : 'textarea-' . uniqid());
    $hasError = ! empty($error);
    $borderColor = $hasError ? 'var(--w-skip)' : 'var(--w-line-2)';
@endphp

<div {{ $attributes->only('class')->merge(['class' => 'w-full']) }}>
    <label
        for="{{ $fieldId }}"
        style="display:block;border:1px solid {{ $borderColor }};border-radius:18px;padding:14px 14px 12px;background:var(--w-paper);transition:border-color 120ms;"
    >
        @if ($label)
            <div style="font-family:var(--font-mono);font-size:10px;font-weight:500;letter-spacing:0.1em;text-transform:uppercase;color:var(--w-muted);margin-bottom:6px;">
                {{ $label }}@if ($required)<span style="color:var(--w-skip);"> *</span>@endif
            </div>
        @endif

        <textarea
            id="{{ $fieldId }}"
            @if ($name) name="{{ $name }}" @endif
            rows="{{ $rows }}"
            @if ($placeholder) placeholder="{{ $placeholder }}" @endif
            @if ($maxlength) maxlength="{{ $maxlength }}" @endif
            @disabled($disabled)
            @required($required)
            {{ $attributes->except('class')->merge([
                'style' => 'width:100%;border:0;outline:0;background:transparent;resize:none;font-family:var(--font-ui);font-size:16px;line-height:1.4;color:var(--w-ink);min-height:44px;padding:0;',
            ]) }}
        >{{ $value }}</textarea>

        @isset($footer)
            <div style="display:flex;align-items:center;justify-content:space-between;margin-top:6px;">
                {{ $footer }}
            </div>
        @endisset
    </label>

    <div style="display:flex;justify-content:space-between;align-items:center;margin-top:6px;gap:8px;">
        <div style="flex:1;min-width:0;">
            @if ($hasError)
                <div style="font-family:var(--font-ui);font-size:12px;color:var(--w-skip);">{{ $error }}</div>
            @elseif ($hint)
                <div style="font-family:var(--font-ui);font-size:12px;color:var(--w-muted);">{{ $hint }}</div>
            @endif
        </div>

        @isset($charCount)
            <div style="font-family:var(--font-mono);font-size:11px;color:var(--w-muted-2);letter-spacing:0.04em;">
                {{ $charCount }}
            </div>
        @endisset
    </div>
</div>
