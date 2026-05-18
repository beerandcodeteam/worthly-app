@props(['kind'])

@php
    $boxStyle = 'width:260px;height:220px;position:relative;display:flex;align-items:center;justify-content:center;';
@endphp

@switch($kind)
    @case('scan')
        <div style="{{ $boxStyle }}">
            <div style="width:180px;height:200px;background:var(--w-paper);border:0.5px solid var(--w-line);border-radius:18px;position:relative;overflow:hidden;box-shadow:0 12px 40px rgba(20,19,15,0.08);">
                <div style="position:absolute;top:22%;right:18%;bottom:22%;left:18%;background:linear-gradient(140deg, #383532 0%, #1A1815 100%);border-radius:14px;"></div>

                @php
                    $corners = [
                        ['top' => 14, 'left' => 14, 'borders' => ['top', 'left']],
                        ['top' => 14, 'right' => 14, 'borders' => ['top', 'right']],
                        ['bottom' => 14, 'left' => 14, 'borders' => ['bottom', 'left']],
                        ['bottom' => 14, 'right' => 14, 'borders' => ['bottom', 'right']],
                    ];
                @endphp

                @foreach ($corners as $corner)
                    @php
                        $positionParts = [];
                        foreach (['top', 'left', 'right', 'bottom'] as $side) {
                            if (isset($corner[$side])) {
                                $positionParts[] = "{$side}:{$corner[$side]}px";
                            }
                        }
                        $borderParts = [];
                        foreach ($corner['borders'] as $side) {
                            $borderParts[] = "border-{$side}:2px solid var(--w-buy)";
                        }
                    @endphp
                    <div
                        aria-hidden="true"
                        style="position:absolute;{{ implode(';', $positionParts) }};width:22px;height:22px;{{ implode(';', $borderParts) }};border-radius:4px;"
                    ></div>
                @endforeach

                <div
                    aria-hidden="true"
                    class="onboarding-scan-line"
                    style="position:absolute;left:14px;right:14px;top:50%;height:2px;background:var(--w-buy);box-shadow:0 0 12px var(--w-buy);"
                ></div>
            </div>
        </div>
        @break

    @case('verdict')
        @php
            $cards = [
                ['v' => 'skip', 'y' => 0,  'x' => -36, 'r' => -8, 'long' => 'Not worth it', 'color' => 'var(--w-skip)'],
                ['v' => 'wait', 'y' => 14, 'x' => 0,   'r' => 0,  'long' => 'Hold off',     'color' => 'var(--w-wait)'],
                ['v' => 'buy',  'y' => 28, 'x' => 36,  'r' => 8,  'long' => 'Worth it',     'color' => 'var(--w-buy)'],
            ];
        @endphp
        <div style="{{ $boxStyle }}">
            <div style="position:relative;width:240px;height:200px;">
                @foreach ($cards as $card)
                    <div style="position:absolute;top:{{ $card['y'] }}px;left:50%;transform:translateX(calc(-50% + {{ $card['x'] }}px)) rotate({{ $card['r'] }}deg);width:130px;padding:14px;background:var(--w-paper);border:0.5px solid var(--w-line);border-radius:14px;box-shadow:0 8px 24px rgba(20,19,15,0.08);">
                        <x-ui.verdict-pill :verdict="$card['v']" size="sm" />
                        <div style="font-family:var(--font-display);font-style:italic;font-size:22px;line-height:1.1;margin-top:10px;color:{{ $card['color'] }};">
                            {{ $card['long'] }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @break

    @case('trio')
        @php
            $tiles = [
                ['v' => 'skip', 'code' => 'SKIP', 'color' => 'var(--w-skip)', 'soft' => 'var(--w-skip-soft)'],
                ['v' => 'wait', 'code' => 'WAIT', 'color' => 'var(--w-wait)', 'soft' => 'var(--w-wait-soft)'],
                ['v' => 'buy',  'code' => 'BUY',  'color' => 'var(--w-buy)',  'soft' => 'var(--w-buy-soft)'],
            ];
        @endphp
        <div style="{{ $boxStyle }}">
            <div style="display:flex;gap:12px;">
                @foreach ($tiles as $tile)
                    <div style="width:72px;height:96px;background:{{ $tile['soft'] }};color:{{ $tile['color'] }};border:0.5px solid {{ $tile['color'] }}33;border-radius:14px;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:8px;">
                        <div aria-hidden="true" style="width:14px;height:14px;border-radius:50%;background:{{ $tile['color'] }};"></div>
                        <div style="font-family:var(--font-mono);font-size:11px;font-weight:600;letter-spacing:0.1em;">{{ $tile['code'] }}</div>
                    </div>
                @endforeach
            </div>
        </div>
        @break
@endswitch
