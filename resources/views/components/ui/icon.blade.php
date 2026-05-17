@props([
    'name',
    'size' => 18,
    'color' => 'currentColor',
    'filled' => false,
])

@php
    $stroke = $color;
    $fillForFilled = $filled ? $color : 'none';
@endphp

@switch($name)
    @case('search')
        <svg {{ $attributes->merge(['class' => 'inline-block']) }} width="{{ $size }}" height="{{ $size }}" viewBox="0 0 24 24" fill="none" stroke="{{ $stroke }}" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <circle cx="11" cy="11" r="7" />
            <path d="m20 20-3.5-3.5" />
        </svg>
        @break

    @case('camera')
        <svg {{ $attributes->merge(['class' => 'inline-block']) }} width="{{ $size }}" height="{{ $size }}" viewBox="0 0 24 24" fill="none" stroke="{{ $stroke }}" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M3 8h3l2-3h8l2 3h3v11H3z" />
            <circle cx="12" cy="13" r="4" />
        </svg>
        @break

    @case('mic')
        <svg {{ $attributes->merge(['class' => 'inline-block']) }} width="{{ $size }}" height="{{ $size }}" viewBox="0 0 24 24" fill="none" stroke="{{ $stroke }}" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <rect x="9" y="3" width="6" height="12" rx="3" />
            <path d="M5 11a7 7 0 0 0 14 0M12 18v3" />
        </svg>
        @break

    @case('sparkle')
        <svg {{ $attributes->merge(['class' => 'inline-block']) }} width="{{ $size }}" height="{{ $size }}" viewBox="0 0 24 24" fill="none" stroke="{{ $stroke }}" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M12 3v6M12 15v6M3 12h6M15 12h6M5.6 5.6l4.2 4.2M14.2 14.2l4.2 4.2M5.6 18.4l4.2-4.2M14.2 9.8l4.2-4.2" />
        </svg>
        @break

    @case('arrow-right')
        <svg {{ $attributes->merge(['class' => 'inline-block']) }} width="{{ $size }}" height="{{ $size }}" viewBox="0 0 24 24" fill="none" stroke="{{ $stroke }}" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M5 12h14M13 5l7 7-7 7" />
        </svg>
        @break

    @case('chevron-left')
        <svg {{ $attributes->merge(['class' => 'inline-block']) }} width="{{ $size }}" height="{{ $size }}" viewBox="0 0 24 24" fill="none" stroke="{{ $stroke }}" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M15 5l-7 7 7 7" />
        </svg>
        @break

    @case('chevron-right')
        <svg {{ $attributes->merge(['class' => 'inline-block']) }} width="{{ $size }}" height="{{ $size }}" viewBox="0 0 24 24" fill="none" stroke="{{ $stroke }}" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M9 5l7 7-7 7" />
        </svg>
        @break

    @case('chevron-down')
        <svg {{ $attributes->merge(['class' => 'inline-block']) }} width="{{ $size }}" height="{{ $size }}" viewBox="0 0 24 24" fill="none" stroke="{{ $stroke }}" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M5 9l7 7 7-7" />
        </svg>
        @break

    @case('close')
        <svg {{ $attributes->merge(['class' => 'inline-block']) }} width="{{ $size }}" height="{{ $size }}" viewBox="0 0 24 24" fill="none" stroke="{{ $stroke }}" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M5 5l14 14M19 5l-14 14" />
        </svg>
        @break

    @case('home')
        <svg {{ $attributes->merge(['class' => 'inline-block']) }} width="{{ $size }}" height="{{ $size }}" viewBox="0 0 24 24" fill="{{ $fillForFilled }}" stroke="{{ $stroke }}" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M3 11l9-7 9 7v9a1 1 0 0 1-1 1h-5v-6h-6v6H4a1 1 0 0 1-1-1z" />
        </svg>
        @break

    @case('clock')
        <svg {{ $attributes->merge(['class' => 'inline-block']) }} width="{{ $size }}" height="{{ $size }}" viewBox="0 0 24 24" fill="{{ $fillForFilled }}" stroke="{{ $stroke }}" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <circle cx="12" cy="12" r="9" fill="{{ $fillForFilled }}" />
            <path d="M12 7v5l3 2" stroke="{{ $filled ? 'var(--w-cream)' : $stroke }}" />
        </svg>
        @break

    @case('user')
        <svg {{ $attributes->merge(['class' => 'inline-block']) }} width="{{ $size }}" height="{{ $size }}" viewBox="0 0 24 24" fill="{{ $fillForFilled }}" stroke="{{ $stroke }}" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <circle cx="12" cy="8" r="4" />
            <path d="M4 21c1-4 4.5-6 8-6s7 2 8 6" />
        </svg>
        @break

    @case('check')
        <svg {{ $attributes->merge(['class' => 'inline-block']) }} width="{{ $size }}" height="{{ $size }}" viewBox="0 0 24 24" fill="none" stroke="{{ $stroke }}" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M4 12l5 5L20 6" />
        </svg>
        @break

    @case('plus')
        <svg {{ $attributes->merge(['class' => 'inline-block']) }} width="{{ $size }}" height="{{ $size }}" viewBox="0 0 24 24" fill="none" stroke="{{ $stroke }}" stroke-width="1.8" stroke-linecap="round" aria-hidden="true">
            <path d="M12 5v14M5 12h14" />
        </svg>
        @break

    @case('bolt')
        <svg {{ $attributes->merge(['class' => 'inline-block']) }} width="{{ $size }}" height="{{ $size }}" viewBox="0 0 24 24" fill="{{ $stroke }}" stroke="none" aria-hidden="true">
            <path d="M13 2L4 14h6l-1 8 9-12h-6z" />
        </svg>
        @break

    @case('apple')
        <svg {{ $attributes->merge(['class' => 'inline-block']) }} width="{{ $size }}" height="{{ $size }}" viewBox="0 0 24 24" fill="{{ $stroke }}" aria-hidden="true">
            <path d="M17.5 12.5c0-2.4 2-3.5 2.1-3.6-1.1-1.6-2.9-1.9-3.5-1.9-1.5-.1-2.9.9-3.7.9-.8 0-1.9-.9-3.2-.8-1.6 0-3.1.9-4 2.4-1.7 2.9-.4 7.2 1.2 9.6.8 1.2 1.8 2.5 3.1 2.4 1.2 0 1.7-.8 3.2-.8s2 .8 3.3.8c1.4 0 2.2-1.2 3.1-2.4.6-.8 1.1-1.8 1.4-2.8-2.9-1.1-3-3.7-3-3.8zM15 4.8c.7-.8 1.1-1.9 1-3-1 0-2.2.7-2.9 1.5-.6.7-1.2 1.8-1 2.9 1.1.1 2.2-.6 2.9-1.4z" />
        </svg>
        @break

    @case('google')
        <svg {{ $attributes->merge(['class' => 'inline-block']) }} width="{{ $size }}" height="{{ $size }}" viewBox="0 0 24 24" aria-hidden="true">
            <path fill="#4285F4" d="M22 12.2c0-.7-.1-1.4-.2-2H12v3.8h5.6c-.2 1.3-1 2.4-2 3.1v2.6h3.3c1.9-1.8 3-4.4 3-7.5z"/>
            <path fill="#34A853" d="M12 22c2.7 0 5-.9 6.7-2.4l-3.3-2.5c-.9.6-2 1-3.4 1-2.6 0-4.8-1.7-5.6-4.1H3v2.6C4.7 19.8 8.1 22 12 22z"/>
            <path fill="#FBBC05" d="M6.4 14C6.2 13.4 6 12.7 6 12s.1-1.4.4-2V7.4H3C2.3 8.8 2 10.4 2 12s.4 3.2 1 4.6L6.4 14z"/>
            <path fill="#EA4335" d="M12 5.9c1.5 0 2.8.5 3.8 1.5l2.9-2.9C16.9 2.9 14.7 2 12 2 8.1 2 4.7 4.2 3 7.4L6.4 10C7.2 7.6 9.4 5.9 12 5.9z"/>
        </svg>
        @break

    @default
        <svg {{ $attributes->merge(['class' => 'inline-block']) }} width="{{ $size }}" height="{{ $size }}" viewBox="0 0 24 24" fill="none" stroke="{{ $stroke }}" stroke-width="1.6" aria-hidden="true">
            <rect x="3" y="3" width="18" height="18" rx="3" />
        </svg>
@endswitch
