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
            Create account
        </div>
        <h1 style="font-family:var(--font-display);font-weight:400;font-size:34px;line-height:1.1;letter-spacing:-0.01em;color:var(--w-ink);margin:0 0 6px;">
            Get your first verdict.
        </h1>
        <p style="font-size:14px;color:var(--w-muted);margin:0;line-height:1.5;">
            Tell us where to save your analyses — name, email, and a password.
        </p>
    </div>

    <form wire:submit="submit" style="display:flex;flex-direction:column;gap:12px;">
        <x-ui.input
            label="Name"
            name="name"
            wire:model="name"
            :error="$errors->first('name')"
            autocomplete="name"
            required
        />

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
            autocomplete="new-password"
            hint="At least 8 characters."
            required
        />

        <x-ui.input
            label="Confirm password"
            name="password_confirmation"
            type="password"
            wire:model="password_confirmation"
            :error="$errors->first('password_confirmation')"
            autocomplete="new-password"
            required
        />

        <div style="margin-top:16px;">
            <x-ui.button type="submit" variant="ink">
                Create account
            </x-ui.button>
        </div>
    </form>

    <div style="text-align:center;font-size:13px;color:var(--w-muted);">
        Already have an account?
        <a
            href="{{ route('login') }}"
            wire:navigate
            style="color:var(--w-ink);font-weight:500;text-decoration:underline;text-underline-offset:3px;"
        >Sign in</a>
    </div>
</div>
