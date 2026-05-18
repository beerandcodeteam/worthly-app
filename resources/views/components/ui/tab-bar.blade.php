@props([
    'active' => 'home',
    'homeHref' => '#',
    'historyHref' => '#',
    'profileHref' => '#',
])

@php
    $tabs = [
        ['key' => 'home', 'href' => $homeHref, 'icon' => 'home', 'label' => 'Ask'],
        ['key' => 'history', 'href' => $historyHref, 'icon' => 'clock', 'label' => 'History'],
        ['key' => 'profile', 'href' => $profileHref, 'icon' => 'user', 'label' => 'You'],
    ];
@endphp

<nav aria-label="Primary" {{ $attributes->merge([
    'style' => 'position:fixed;left:0;right:0;bottom:0;padding:0 16px 22px 16px;z-index:40;pointer-events:none;',
]) }}>
    <div style="pointer-events:auto;background:rgba(255,255,255,0.82);backdrop-filter:blur(20px) saturate(180%);-webkit-backdrop-filter:blur(20px) saturate(180%);border:0.5px solid var(--w-line-2);border-radius:22px;padding:8px 6px;display:flex;justify-content:space-around;box-shadow:0 8px 24px rgba(20,19,15,0.08);max-width:480px;margin:0 auto;">
        @foreach ($tabs as $tab)
            @php
                $isActive = $active === $tab['key'];
                $color = $isActive ? 'var(--w-ink)' : 'var(--w-muted-2)';
            @endphp
            <a
                href="{{ $tab['href'] }}"
                wire:navigate
                data-testid="tab-{{ $tab['key'] }}"
                aria-current="{{ $isActive ? 'page' : 'false' }}"
                style="flex:1;text-decoration:none;padding:8px 4px 6px;display:flex;flex-direction:column;align-items:center;gap:3px;color:{{ $color }};"
            >
                <x-ui.icon :name="$tab['icon']" :size="22" :filled="$isActive" :color="$color" />
                <span style="font-family:var(--font-mono);font-size:10px;font-weight:500;letter-spacing:0.08em;text-transform:uppercase;">{{ $tab['label'] }}</span>
            </a>
        @endforeach
    </div>
</nav>
