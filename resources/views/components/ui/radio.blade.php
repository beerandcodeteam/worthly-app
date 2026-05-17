@props([
    'label' => null,
    'name',
    'value',
    'id' => null,
    'checked' => false,
    'disabled' => false,
    'hint' => null,
])

@php
    $fieldId = $id ?? 'radio-' . $name . '-' . md5((string) $value);
@endphp

<label
    for="{{ $fieldId }}"
    style="display:flex;align-items:flex-start;gap:10px;cursor:{{ $disabled ? 'not-allowed' : 'pointer' }};opacity:{{ $disabled ? '0.5' : '1' }};"
>
    <span style="position:relative;display:inline-flex;flex-shrink:0;width:20px;height:20px;margin-top:1px;">
        <input
            id="{{ $fieldId }}"
            type="radio"
            name="{{ $name }}"
            value="{{ $value }}"
            @checked($checked)
            @disabled($disabled)
            {{ $attributes->merge([
                'style' => 'appearance:none;-webkit-appearance:none;width:20px;height:20px;border:1px solid var(--w-line-2);background:var(--w-paper);border-radius:50%;margin:0;cursor:inherit;',
            ]) }}
        />
        <span aria-hidden="true" class="ui-radio-dot" style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);width:8px;height:8px;border-radius:50%;background:#FAF8F2;opacity:0;pointer-events:none;transition:opacity 100ms;"></span>
    </span>

    @if ($label || trim($slot) !== '')
        <span style="flex:1;min-width:0;">
            <span style="display:block;font-family:var(--font-ui);font-size:14px;line-height:1.4;color:var(--w-ink);">
                {{ $label ?? $slot }}
            </span>
            @if ($hint)
                <span style="display:block;font-family:var(--font-ui);font-size:12px;color:var(--w-muted);margin-top:2px;">{{ $hint }}</span>
            @endif
        </span>
    @endif
</label>

<style>
    input[type="radio"]:checked + .ui-radio-dot { opacity: 1; }
    input[type="radio"]:checked { background: var(--w-ink) !important; border-color: var(--w-ink) !important; }
</style>
