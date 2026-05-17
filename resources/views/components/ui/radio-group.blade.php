@props([
    'label' => null,
    'name',
    'value' => null,
    'options' => [],
    'hint' => null,
    'error' => null,
    'inline' => false,
])

@php
    $hasError = ! empty($error);
@endphp

<fieldset {{ $attributes->merge(['style' => 'border:0;padding:0;margin:0;width:100%;']) }}>
    @if ($label)
        <legend style="font-family:var(--font-mono);font-size:10px;font-weight:500;letter-spacing:0.14em;text-transform:uppercase;color:var(--w-muted);margin-bottom:10px;padding:0;">
            {{ $label }}
        </legend>
    @endif

    <div style="display:flex;flex-direction:{{ $inline ? 'row' : 'column' }};gap:{{ $inline ? '16px' : '10px' }};flex-wrap:wrap;">
        @if (! empty($options))
            @foreach ($options as $optionValue => $optionLabel)
                <x-ui.radio
                    :name="$name"
                    :value="$optionValue"
                    :label="$optionLabel"
                    :checked="(string) $value === (string) $optionValue"
                />
            @endforeach
        @else
            {{ $slot }}
        @endif
    </div>

    @if ($hasError)
        <div style="margin-top:8px;font-family:var(--font-ui);font-size:12px;color:var(--w-skip);">{{ $error }}</div>
    @elseif ($hint)
        <div style="margin-top:8px;font-family:var(--font-ui);font-size:12px;color:var(--w-muted);">{{ $hint }}</div>
    @endif
</fieldset>
