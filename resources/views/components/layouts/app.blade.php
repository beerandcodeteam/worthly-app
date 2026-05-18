@props([
    'title' => null,
    'activeTab' => null,
    'showTabBar' => true,
    'header' => null,
    'homeHref' => null,
    'historyHref' => null,
    'profileHref' => null,
])

@php
    $resolvedHomeHref = $homeHref ?? route('home');
    $resolvedHistoryHref = $historyHref ?? route('history');
    $resolvedProfileHref = $profileHref ?? route('profile');

    $currentRouteName = optional(request()->route())->getName();
    $resolvedActiveTab = $activeTab ?? match (true) {
        $currentRouteName === 'history' => 'history',
        $currentRouteName === 'profile' => 'profile',
        default => 'home',
    };
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover, maximum-scale=1.0, user-scalable=no">
        <meta name="theme-color" content="#F2EFE6">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="default">
        <meta name="format-detection" content="telephone=no">

        <title>{{ $title ?? config('app.name') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @livewireStyles
    </head>
    <body style="background:var(--w-cream);min-height:100vh;color:var(--w-ink);">
        <div class="worthly-app-root" style="min-height:100vh;display:flex;flex-direction:column;background:var(--w-cream);">
            @if ($header)
                {{ $header }}
            @endif

            <main style="flex:1;overflow-y:auto;padding-bottom:{{ $showTabBar ? '110px' : '24px' }};">
                {{ $slot }}
            </main>

            @if ($showTabBar)
                <x-ui.tab-bar
                    :active="$resolvedActiveTab"
                    :homeHref="$resolvedHomeHref"
                    :historyHref="$resolvedHistoryHref"
                    :profileHref="$resolvedProfileHref"
                />
            @endif
        </div>

        @livewireScripts
    </body>
</html>
