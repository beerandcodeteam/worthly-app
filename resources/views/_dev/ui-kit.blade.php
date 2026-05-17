<x-layouts.guest title="Worthly UI Kit">
    <div style="max-width:520px;margin:0 auto;padding:40px 22px 80px;display:flex;flex-direction:column;gap:48px;">

        {{-- Wordmark + intro --}}
        <header style="display:flex;flex-direction:column;gap:8px;">
            <div style="font-family:var(--font-mono);font-size:11px;letter-spacing:0.14em;text-transform:uppercase;color:var(--w-muted);">Style guide</div>
            <h1 style="font-family:var(--font-display);font-style:italic;font-size:44px;line-height:1.05;color:var(--w-ink);margin:0;">Worthly UI Kit</h1>
            <p style="font-size:14px;line-height:1.5;color:var(--w-muted);margin:0;">Every primitive on one page. Use this to eyeball regressions when tokens or components change.</p>
        </header>

        {{-- TYPOGRAPHY --}}
        <section style="display:flex;flex-direction:column;gap:14px;">
            <x-ui.section-label label="Typography" />
            <div style="display:flex;flex-direction:column;gap:6px;">
                <div style="font-family:var(--font-display);font-style:italic;font-size:40px;line-height:1.05;color:var(--w-ink);">Is it <em>actually</em> worth it?</div>
                <div style="font-family:var(--font-ui);font-size:16px;color:var(--w-ink);">Body text — Geist 16/24 for paragraphs and rows.</div>
                <div style="font-family:var(--font-mono);font-size:11px;letter-spacing:0.14em;text-transform:uppercase;color:var(--w-muted);">Mono caption · Geist Mono</div>
            </div>
        </section>

        {{-- COLORS --}}
        <section style="display:flex;flex-direction:column;gap:14px;">
            <x-ui.section-label label="Colors" />
            <div style="display:grid;grid-template-columns:repeat(3, minmax(0,1fr));gap:10px;">
                @php
                    $swatches = [
                        ['name' => 'cream',   'token' => 'var(--w-cream)'],
                        ['name' => 'cream-2', 'token' => 'var(--w-cream-2)'],
                        ['name' => 'paper',   'token' => 'var(--w-paper)'],
                        ['name' => 'ink',     'token' => 'var(--w-ink)'],
                        ['name' => 'ink-2',   'token' => 'var(--w-ink-2)'],
                        ['name' => 'muted',   'token' => 'var(--w-muted)'],
                        ['name' => 'buy',     'token' => 'var(--w-buy)'],
                        ['name' => 'wait',    'token' => 'var(--w-wait)'],
                        ['name' => 'skip',    'token' => 'var(--w-skip)'],
                    ];
                @endphp
                @foreach ($swatches as $swatch)
                    <div style="display:flex;flex-direction:column;gap:6px;">
                        <div style="width:100%;height:48px;border-radius:10px;background:{{ $swatch['token'] }};border:0.5px solid var(--w-line);"></div>
                        <div style="font-family:var(--font-mono);font-size:10px;letter-spacing:0.08em;color:var(--w-muted);">--w-{{ $swatch['name'] }}</div>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- BUTTONS --}}
        <section style="display:flex;flex-direction:column;gap:14px;">
            <x-ui.section-label label="Buttons" />
            <x-ui.button variant="ink">
                Sign in
                <x-ui.icon name="arrow-right" :size="16" color="#FAF8F2" />
            </x-ui.button>
            <x-ui.button variant="paper">Continue with Apple</x-ui.button>
            <x-ui.button variant="buy">Looks good — buy now</x-ui.button>
            <x-ui.button variant="ink" :disabled="true">Disabled</x-ui.button>
            <div style="display:flex;gap:10px;">
                <x-ui.button variant="ink" size="sm" :full="false">Small</x-ui.button>
                <x-ui.button variant="ink" size="md" :full="false">Medium</x-ui.button>
                <x-ui.button variant="ink" size="lg" :full="false">Large</x-ui.button>
            </div>
        </section>

        {{-- FORM CONTROLS --}}
        <section style="display:flex;flex-direction:column;gap:14px;">
            <x-ui.section-label label="Form controls" />

            <x-ui.input
                label="Email"
                name="email"
                type="email"
                value="hi@example.com"
                placeholder="you@example.com"
            />

            <x-ui.input
                label="Password"
                name="password"
                type="password"
                value="••••••••"
                hint="At least 8 characters."
            />

            <x-ui.input
                label="API key"
                name="bad"
                value="invalid"
                error="That key doesn't match any active account."
            />

            <x-ui.textarea
                label="Ask Worthly"
                name="composer"
                rows="3"
                placeholder="Is the Steam Deck OLED worth it in 2026?"
                value="Is the Sony WH-1000XM5 worth it at $328?"
            >
                <x-slot:charCount>42 / 1000</x-slot:charCount>
            </x-ui.textarea>

            <x-ui.select
                label="Notify me"
                name="notify"
                :options="['email' => 'Email me', 'push' => 'Push notification', 'none' => 'Don\'t notify me']"
                value="push"
            />

            <div style="display:flex;flex-direction:column;gap:10px;">
                <x-ui.checkbox label="Email me when this drops below fair price" :checked="true" />
                <x-ui.checkbox label="Send me weekly digest" />
            </div>

            <x-ui.radio-group
                label="Default verdict bias"
                name="bias"
                value="balanced"
                :options="['frugal' => 'Frugal — favor Wait', 'balanced' => 'Balanced', 'eager' => 'Eager — favor Buy']"
            />
        </section>

        {{-- VERDICT PILLS --}}
        <section style="display:flex;flex-direction:column;gap:14px;">
            <x-ui.section-label label="Verdict pills" />
            <div style="display:flex;flex-wrap:wrap;gap:8px;align-items:center;">
                <x-ui.verdict-pill verdict="buy" size="sm" />
                <x-ui.verdict-pill verdict="wait" size="sm" />
                <x-ui.verdict-pill verdict="skip" size="sm" />
            </div>
            <div style="display:flex;flex-wrap:wrap;gap:8px;align-items:center;">
                <x-ui.verdict-pill verdict="buy" />
                <x-ui.verdict-pill verdict="wait" />
                <x-ui.verdict-pill verdict="skip" />
            </div>
            <div style="display:flex;flex-wrap:wrap;gap:8px;align-items:center;">
                <x-ui.verdict-pill verdict="buy" size="lg" />
                <x-ui.verdict-pill verdict="wait" size="lg" />
                <x-ui.verdict-pill verdict="skip" size="lg" />
            </div>
        </section>

        {{-- ICONS --}}
        <section style="display:flex;flex-direction:column;gap:14px;">
            <x-ui.section-label label="Icons" />
            <div style="display:grid;grid-template-columns:repeat(6, minmax(0,1fr));gap:12px;color:var(--w-ink);">
                @foreach (['search','camera','mic','sparkle','arrow-right','chevron-left','chevron-right','chevron-down','close','home','clock','user','check','plus','bolt','apple','google'] as $icon)
                    <div style="display:flex;flex-direction:column;align-items:center;gap:6px;">
                        <x-ui.icon :name="$icon" :size="22" />
                        <div style="font-family:var(--font-mono);font-size:9px;letter-spacing:0.05em;color:var(--w-muted-2);">{{ $icon }}</div>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- CARD + HAIRLINE + PRODUCT IMAGE --}}
        <section style="display:flex;flex-direction:column;gap:14px;">
            <x-ui.section-label label="Card, hairline, product image" />
            <x-ui.card>
                <div style="display:flex;gap:12px;align-items:center;">
                    <x-ui.product-image tone="#383532" accent="#C8B68A" brand="Logi" :size="56" :radius="10" />
                    <div style="flex:1;">
                        <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px;">
                            <x-ui.verdict-pill verdict="wait" size="sm" />
                            <span style="font-family:var(--font-mono);font-size:10px;color:var(--w-muted-2);letter-spacing:0.06em;">2h</span>
                        </div>
                        <div style="font-size:14px;font-weight:500;color:var(--w-ink);">Logitech MX Master 3S</div>
                        <div style="font-size:12px;color:var(--w-muted);">I'd hold off ~2 weeks — MX Master 4 likely soon.</div>
                    </div>
                </div>
                <x-ui.hairline style="margin:14px 0;" />
                <div style="display:flex;justify-content:space-between;font-family:var(--font-mono);font-size:11px;letter-spacing:0.04em;color:var(--w-muted);">
                    <span>$89 at B&amp;H</span>
                    <span>−10% vs avg</span>
                </div>
            </x-ui.card>
        </section>

        {{-- SCREEN HEADER --}}
        <section style="display:flex;flex-direction:column;gap:14px;">
            <x-ui.section-label label="Screen header" />
            <div style="background:var(--w-cream-2);border-radius:18px;overflow:hidden;border:0.5px solid var(--w-line);">
                <x-ui.screen-header
                    eyebrow="Analysis"
                    title="Logitech MX Master 3S"
                    backHref="#"
                    closeHref="#"
                />
                <div style="padding:14px 18px 22px;color:var(--w-muted);font-size:13px;">Header preview — back chevron · title · close.</div>
            </div>
        </section>

        {{-- TAB BAR --}}
        <section style="display:flex;flex-direction:column;gap:14px;">
            <x-ui.section-label label="Tab bar" />
            <div style="position:relative;height:120px;background:var(--w-cream-2);border-radius:18px;overflow:hidden;border:0.5px solid var(--w-line);">
                <div style="position:absolute;left:16px;right:16px;bottom:22px;">
                    <div style="background:rgba(255,255,255,0.92);border:0.5px solid var(--w-line-2);border-radius:22px;padding:8px 6px;display:flex;justify-content:space-around;box-shadow:0 8px 24px rgba(20,19,15,0.08);">
                        @foreach ([['key'=>'home','icon'=>'home','label'=>'Ask'],['key'=>'history','icon'=>'clock','label'=>'History'],['key'=>'profile','icon'=>'user','label'=>'You']] as $tab)
                            @php $isActive = $tab['key'] === 'home'; $color = $isActive ? 'var(--w-ink)' : 'var(--w-muted-2)'; @endphp
                            <div style="flex:1;padding:8px 4px 6px;display:flex;flex-direction:column;align-items:center;gap:3px;color:{{ $color }};">
                                <x-ui.icon :name="$tab['icon']" :size="22" :filled="$isActive" :color="$color" />
                                <span style="font-family:var(--font-mono);font-size:10px;font-weight:500;letter-spacing:0.08em;text-transform:uppercase;">{{ $tab['label'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        {{-- MODAL --}}
        <section style="display:flex;flex-direction:column;gap:14px;">
            <x-ui.section-label label="Modal" />
            <div style="position:relative;height:340px;background:rgba(20,19,15,0.18);border-radius:18px;overflow:hidden;border:0.5px solid var(--w-line);">
                <div style="position:absolute;left:0;right:0;bottom:0;">
                    <div style="background:var(--w-cream);border-radius:22px 22px 0 0;box-shadow:0 -12px 40px rgba(20,19,15,0.18);">
                        <div style="padding:18px 18px 12px;display:flex;align-items:center;justify-content:space-between;border-bottom:0.5px solid var(--w-line);">
                            <div>
                                <div style="font-family:var(--font-mono);font-size:10px;letter-spacing:0.12em;text-transform:uppercase;color:var(--w-muted);margin-bottom:4px;">Heads up</div>
                                <div style="font-family:var(--font-display);font-style:italic;font-size:24px;line-height:1.1;color:var(--w-ink);">Skip this and grab the Pro 3.</div>
                            </div>
                            <a href="#" aria-label="Close" style="appearance:none;border:1px solid var(--w-line);background:transparent;width:32px;height:32px;border-radius:999px;display:inline-flex;align-items:center;justify-content:center;text-decoration:none;color:var(--w-ink);">
                                <x-ui.icon name="close" :size="18" />
                            </a>
                        </div>
                        <div style="padding:18px;color:var(--w-muted);font-size:14px;line-height:1.5;">
                            The AirPods Pro 3 was announced last week and ships in ten days. Better ANC, better fit, same price tier.
                        </div>
                        <div style="padding:14px 18px 22px;border-top:0.5px solid var(--w-line);display:flex;flex-direction:column;gap:10px;">
                            <x-ui.button variant="ink">Got it</x-ui.button>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <footer style="font-family:var(--font-mono);font-size:10px;letter-spacing:0.1em;color:var(--w-muted-2);text-align:center;text-transform:uppercase;">
            /_dev/ui-kit · Worthly design tokens
        </footer>
    </div>
</x-layouts.guest>
