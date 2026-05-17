<div>
    @if (! $confirming)
        <button
            type="button"
            wire:click="confirm"
            data-testid="sign-out-trigger"
            style="appearance:none;background:transparent;border:1px solid var(--w-line-2);border-radius:14px;padding:14px 18px;width:100%;font-family:var(--font-ui);font-size:15px;color:var(--w-skip);cursor:pointer;text-align:center;"
        >Sign out</button>
    @else
        <div
            role="alertdialog"
            data-testid="sign-out-confirm"
            style="border:1px solid var(--w-line-2);border-radius:16px;padding:18px;background:var(--w-paper);display:flex;flex-direction:column;gap:14px;"
        >
            <div>
                <div style="font-family:var(--font-display);font-size:18px;color:var(--w-ink);margin-bottom:6px;">
                    Sign out of Worthly?
                </div>
                <div style="font-family:var(--font-ui);font-size:13px;color:var(--w-muted);line-height:1.45;">
                    Your saved analyses will be waiting next time you sign in.
                </div>
            </div>

            <div style="display:flex;gap:10px;">
                <button
                    type="button"
                    wire:click="cancel"
                    data-testid="sign-out-cancel"
                    style="flex:1;appearance:none;background:transparent;border:1px solid var(--w-line-2);border-radius:12px;padding:10px 16px;font-family:var(--font-ui);font-size:14px;color:var(--w-ink);cursor:pointer;"
                >Cancel</button>
                <button
                    type="button"
                    wire:click="signOut"
                    data-testid="sign-out-confirm-button"
                    style="flex:1;appearance:none;background:var(--w-skip);border:0;border-radius:12px;padding:10px 16px;font-family:var(--font-ui);font-size:14px;color:#FAF8F2;cursor:pointer;"
                >Sign out</button>
            </div>
        </div>
    @endif
</div>
