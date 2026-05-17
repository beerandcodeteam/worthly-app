@props([
    'tone' => '#3A3835',
    'accent' => '#C8B68A',
    'brand' => null,
    'size' => 80,
    'radius' => 12,
])

@php
    $sizePx = (int) $size;
    $radiusPx = (int) $radius;
    $brandLabel = $brand ? strtoupper(substr((string) $brand, 0, 4)) : '';

    $containerStyle = sprintf(
        'width:%dpx;height:%dpx;border-radius:%dpx;background:linear-gradient(140deg,%s 0%%,#1A1815 60%%,%s 100%%);position:relative;overflow:hidden;flex-shrink:0;box-shadow:inset 0 0 0 0.5px rgba(255,255,255,0.05);',
        $sizePx,
        $sizePx,
        $radiusPx,
        $tone,
        $tone,
    );

    $blobStyle = sprintf(
        'position:absolute;left:18%%;top:22%%;width:64%%;height:56%%;background:radial-gradient(ellipse 80%% 70%% at 40%% 35%%,%s55 0%%,transparent 60%%),linear-gradient(180deg,%s30 0%%,transparent 80%%);border-radius:46%% 54%% 50%% 50%% / 50%% 40%% 60%% 50%%;filter:blur(0.3px);',
        $accent,
        $accent,
    );

    $highlightStyle = sprintf(
        'position:absolute;left:24%%;top:40%%;width:52%%;height:28%%;background:%saa;border-radius:50%% 50%% 40%% 40%%;opacity:0.55;',
        $accent,
    );
@endphp

<div {{ $attributes->merge(['style' => $containerStyle, 'role' => 'img', 'aria-label' => 'Product image']) }}>
    <div aria-hidden="true" style="{{ $blobStyle }}"></div>
    <div aria-hidden="true" style="{{ $highlightStyle }}"></div>

    @if ($brandLabel)
        <div aria-hidden="true" style="position:absolute;bottom:4px;right:6px;font-family:var(--font-mono);font-size:8px;font-weight:500;color:rgba(255,255,255,0.45);letter-spacing:0.1em;">
            {{ $brandLabel }}
        </div>
    @endif
</div>
