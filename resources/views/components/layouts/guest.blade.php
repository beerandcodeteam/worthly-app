@props([
    'title' => null,
])

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
        <div class="worthly-guest-root" style="min-height:100vh;display:flex;flex-direction:column;background:var(--w-cream);">
            {{ $slot }}
        </div>

        @livewireScripts
    </body>
</html>
