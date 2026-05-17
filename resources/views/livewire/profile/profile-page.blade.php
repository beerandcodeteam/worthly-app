<div
    data-testid="profile-page"
    style="display:flex;flex-direction:column;flex:1;padding:60px 0 24px;background:var(--w-cream);min-height:100vh;"
>
    {{-- Header --}}
    <div style="display:flex;justify-content:space-between;align-items:center;padding:0 22px;margin-bottom:18px;">
        <div>
            <div style="font-family:var(--font-mono);font-size:11px;letter-spacing:0.14em;text-transform:uppercase;color:var(--w-muted);margin-bottom:10px;">You</div>
            <h1 style="font-family:var(--font-display);font-weight:400;font-size:36px;line-height:1;letter-spacing:-0.01em;color:var(--w-ink);margin:0;">Profile</h1>
        </div>
        <button
            type="button"
            wire:click="refresh"
            data-testid="profile-refresh"
            aria-label="Pull to refresh"
            style="appearance:none;background:transparent;border:0.5px solid var(--w-line-2);border-radius:999px;padding:6px 12px;font-family:var(--font-mono);font-size:10px;color:var(--w-muted);letter-spacing:0.08em;cursor:pointer;"
        >{{ $refreshing ? 'Refreshing…' : 'Refresh' }}</button>
    </div>

    {{-- Identity card --}}
    <div style="padding:0 18px 18px;">
        <div
            data-testid="profile-identity"
            style="background:var(--w-paper);border:0.5px solid var(--w-line-2);border-radius:16px;padding:18px;"
        >
            <div style="display:flex;align-items:center;gap:14px;">
                <div
                    data-testid="profile-avatar"
                    style="width:56px;height:56px;border-radius:50%;background:var(--w-ink);color:#FAF8F2;display:flex;align-items:center;justify-content:center;font-family:var(--font-display);font-style:italic;font-size:24px;"
                >{{ $avatarInitial }}</div>
                <div style="flex:1;min-width:0;">
                    <div
                        data-testid="profile-name"
                        style="font-family:var(--font-ui);font-size:16px;font-weight:500;color:var(--w-ink);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"
                    >{{ $name ?? '—' }}</div>
                    <div
                        data-testid="profile-email"
                        style="font-family:var(--font-ui);font-size:13px;color:var(--w-muted);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"
                    >{{ $email ?? '—' }}</div>
                </div>
            </div>

            <div
                data-testid="profile-stats"
                style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:0;margin-top:16px;padding-top:14px;border-top:0.5px solid var(--w-line);"
            >
                <div style="text-align:center;">
                    <span
                        data-testid="stat-total-analyses"
                        style="font-family:var(--font-display);font-size:20px;font-weight:600;color:var(--w-ink);display:block;"
                    >{{ $totalAnalyses }}</span>
                    <span style="font-family:var(--font-mono);font-size:10px;color:var(--w-muted);letter-spacing:0.08em;text-transform:uppercase;">Analyses</span>
                </div>
                <div style="text-align:center;">
                    <span
                        data-testid="stat-saved-products"
                        style="font-family:var(--font-display);font-size:20px;font-weight:600;color:var(--w-ink);display:block;"
                    >{{ $savedProducts }}</span>
                    <span style="font-family:var(--font-mono);font-size:10px;color:var(--w-muted);letter-spacing:0.08em;text-transform:uppercase;">Saved</span>
                </div>
                <div style="text-align:center;">
                    <span
                        data-testid="stat-money-saved"
                        style="font-family:var(--font-display);font-size:20px;font-weight:600;color:var(--w-ink);display:block;"
                    >{{ $moneySaved }}</span>
                    <span style="font-family:var(--font-mono);font-size:10px;color:var(--w-muted);letter-spacing:0.08em;text-transform:uppercase;">$ saved</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Plan section label --}}
    <div style="padding:0 22px 8px;">
        <div style="font-family:var(--font-mono);font-size:10px;font-weight:500;letter-spacing:0.12em;text-transform:uppercase;color:var(--w-muted);">Plan</div>
    </div>

    {{-- Plan card --}}
    <div style="padding:0 18px 18px;">
        <div
            data-testid="plan-card"
            style="background:linear-gradient(135deg, var(--w-cream-2) 0%, var(--w-paper) 100%);border:0.5px solid var(--w-line-2);border-radius:16px;padding:16px;"
        >
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
                <div>
                    <div style="font-family:var(--font-mono);font-size:10px;font-weight:500;letter-spacing:0.12em;text-transform:uppercase;color:var(--w-muted);">Current plan</div>
                    <div
                        data-testid="plan-name"
                        style="font-family:var(--font-display);font-style:italic;font-size:24px;color:var(--w-ink);margin-top:2px;"
                    >Free</div>
                </div>
                <div
                    data-testid="plan-usage"
                    style="font-family:var(--font-display);font-size:28px;font-weight:500;color:var(--w-ink);"
                >{{ $totalAnalyses }}<span style="font-size:14px;color:var(--w-muted-2);">/{{ \App\Livewire\Profile\ProfilePage::FREE_PLAN_QUOTA }}</span></div>
            </div>
            <div
                data-testid="plan-progress"
                style="height:4px;background:var(--w-line);border-radius:2px;overflow:hidden;margin-bottom:14px;"
            >
                <div style="width:{{ $planUsagePercent }}%;height:100%;background:var(--w-ink);"></div>
            </div>

            <button
                type="button"
                data-testid="upgrade-cta"
                title="{{ $upgradeTooltip }}"
                aria-disabled="{{ $upgradeDisabled ? 'true' : 'false' }}"
                @disabled($upgradeDisabled)
                style="appearance:none;display:flex;align-items:center;justify-content:center;gap:8px;width:100%;height:44px;background:var(--w-ink);color:#FAF8F2;border:0;border-radius:12px;font-family:var(--font-ui);font-size:14px;font-weight:500;cursor:{{ $upgradeDisabled ? 'not-allowed' : 'pointer' }};opacity:{{ $upgradeDisabled ? '0.55' : '1' }};"
            >Upgrade to Pro</button>
        </div>
    </div>

    {{-- Settings (placeholder rows) --}}
    <div style="padding:0 22px 8px;">
        <div style="font-family:var(--font-mono);font-size:10px;font-weight:500;letter-spacing:0.12em;text-transform:uppercase;color:var(--w-muted);">Settings</div>
    </div>
    <div style="padding:0 18px 18px;">
        <div
            data-testid="profile-settings"
            style="background:var(--w-paper);border:0.5px solid var(--w-line-2);border-radius:16px;overflow:hidden;"
        >
            @foreach ([
                ['l' => 'Saved products', 'v' => (string) $savedProducts],
                ['l' => 'Notifications', 'v' => 'On'],
                ['l' => 'Currency', 'v' => 'USD'],
                ['l' => 'Region', 'v' => '—'],
                ['l' => 'About Worthly', 'v' => 'v1.0.0'],
            ] as $i => $row)
                <div style="display:flex;justify-content:space-between;align-items:center;padding:14px 16px;{{ $i > 0 ? 'border-top:0.5px solid var(--w-line);' : '' }}">
                    <span style="font-family:var(--font-ui);font-size:14px;color:var(--w-ink);">{{ $row['l'] }}</span>
                    <span style="font-family:var(--font-ui);font-size:13px;color:var(--w-muted);">{{ $row['v'] }}</span>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Sign out --}}
    <div style="padding:0 18px 32px;">
        <livewire:profile.sign-out-action />
    </div>
</div>
