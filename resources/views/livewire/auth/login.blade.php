<div style="display:flex;flex-direction:column;flex:1;padding:70px 28px 28px;gap:24px;">
    <div style="display:flex;justify-content:space-between;align-items:center;">
        <a
            href="{{ route('onboarding') }}"
            wire:navigate
            style="background:transparent;border:0;color:var(--w-ink);text-decoration:none;font-family:var(--font-ui);font-size:13px;"
        >&larr;</a>
        <span style="font-family:var(--font-display);font-size:18px;color:var(--w-ink);">Worthly</span>
        <span style="width:32px;"></span>
    </div>

    <div>
        <div style="font-family:var(--font-mono);font-size:11px;font-weight:500;letter-spacing:0.14em;text-transform:uppercase;color:var(--w-muted);margin-bottom:12px;">
            Sign in
        </div>
        <h1 style="font-family:var(--font-display);font-weight:400;font-size:38px;line-height:1.05;letter-spacing:-0.01em;color:var(--w-ink);margin:0 0 6px;">
            Welcome back.
        </h1>
        <p style="font-size:14px;color:var(--w-muted);margin:0;line-height:1.5;">
            Sign in to keep your analysis history and saved products.
        </p>
    </div>

    @if ($formError)
        <div
            role="alert"
            data-testid="form-error"
            style="border:1px solid var(--w-skip);background:rgba(168,57,44,0.06);color:var(--w-skip);padding:10px 14px;border-radius:12px;font-family:var(--font-ui);font-size:13px;"
        >
            {{ $formError }}
        </div>
    @endif

    <form wire:submit="submit" style="display:flex;flex-direction:column;gap:12px;">
        <x-ui.input
            label="Email"
            name="email"
            type="email"
            wire:model="email"
            :error="$errors->first('email')"
            autocomplete="email"
            inputmode="email"
            required
        />

        <x-ui.input
            label="Password"
            name="password"
            type="password"
            wire:model="password"
            :error="$errors->first('password')"
            autocomplete="current-password"
            required
        />

        <button
            type="button"
            @disabled(! $forgotPasswordEnabled)
            data-testid="forgot-password"
            title="Coming soon"
            style="appearance:none;background:transparent;border:0;align-self:flex-end;margin-top:4px;padding:4px;font-family:var(--font-ui);font-size:12px;color:var(--w-muted);cursor:{{ $forgotPasswordEnabled ? 'pointer' : 'not-allowed' }};opacity:{{ $forgotPasswordEnabled ? '1' : '0.6' }};"
        >Forgot password?</button>

        <div style="margin-top:12px;">
            <x-ui.button type="submit" variant="ink">Sign in</x-ui.button>
        </div>
    </form>

    <div style="display:flex;align-items:center;gap:10px;padding:12px 0;">
        <div style="flex:1;height:1px;background:var(--w-line);"></div>
        <span style="font-family:var(--font-mono);font-size:10px;color:var(--w-muted);letter-spacing:0.12em;text-transform:uppercase;">Or continue with</span>
        <div style="flex:1;height:1px;background:var(--w-line);"></div>
    </div>

    <div style="display:flex;gap:10px;">
        <button
            type="button"
            @disabled(! $ssoEnabled)
            data-testid="sso-apple"
            title="Coming soon"
            style="flex:1;appearance:none;background:var(--w-paper);border:1px solid var(--w-line-2);border-radius:14px;height:48px;font-family:var(--font-ui);font-size:14px;color:var(--w-ink);cursor:{{ $ssoEnabled ? 'pointer' : 'not-allowed' }};opacity:{{ $ssoEnabled ? '1' : '0.55' }};"
        >Apple</button>
        <button
            type="button"
            @disabled(! $ssoEnabled)
            data-testid="sso-google"
            title="Coming soon"
            style="flex:1;appearance:none;background:var(--w-paper);border:1px solid var(--w-line-2);border-radius:14px;height:48px;font-family:var(--font-ui);font-size:14px;color:var(--w-ink);cursor:{{ $ssoEnabled ? 'pointer' : 'not-allowed' }};opacity:{{ $ssoEnabled ? '1' : '0.55' }};"
        >Google</button>
    </div>

    <div style="text-align:center;font-size:13px;color:var(--w-muted);">
        New here?
        <a
            href="{{ route('register') }}"
            wire:navigate
            style="color:var(--w-ink);font-weight:500;text-decoration:underline;text-underline-offset:3px;"
        >Create an account</a>
    </div>
</div>
