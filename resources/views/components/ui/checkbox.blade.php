@props([
    'label' => null,
    'name' => null,
    'id' => null,
    'value' => '1',
    'checked' => false,
    'disabled' => false,
    'hint' => null,
])

@php
    $fieldId = $id ?? ($name ? 'checkbox-' . $name : 'checkbox-' . uniqid());
@endphp

<label
    for="{{ $fieldId }}"
    style="display:flex;align-items:flex-start;gap:10px;cursor:{{ $disabled ? 'not-allowed' : 'pointer' }};opacity:{{ $disabled ? '0.5' : '1' }};"
>
    <span style="position:relative;display:inline-flex;flex-shrink:0;width:20px;height:20px;margin-top:1px;">
        <input
            id="{{ $fieldId }}"
            type="checkbox"
            @if ($name) name="{{ $name }}" @endif
            value="{{ $value }}"
            @checked($checked)
            @disabled($disabled)
            {{ $attributes->merge([
                'style' => 'appearance:none;-webkit-appearance:none;width:20px;height:20px;border:1px solid var(--w-line-2);background:var(--w-paper);border-radius:6px;margin:0;cursor:inherit;',
            ]) }}
        />
        <span aria-hidden="true" style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;color:#FAF8F2;pointer-events:none;">
            <x-ui.icon name="check" :size="12" color="#FAF8F2" class="ui-check-mark" />
        </span>
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
    input[type="checkbox"]:checked + span .ui-check-mark { opacity: 1; }
    input[type="checkbox"] + span .ui-check-mark { opacity: 0; transition: opacity 100ms; }
    input[type="checkbox"]:checked { background: var(--w-ink) !important; border-color: var(--w-ink) !important; }
</style>
