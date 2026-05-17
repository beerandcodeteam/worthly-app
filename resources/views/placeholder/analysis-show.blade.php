<x-layouts.app :title="'Analysis ' . $analysis" active-tab="home">
    <div style="padding:80px 24px;text-align:center;font-family:var(--font-ui);color:var(--w-muted);">
        <div style="font-family:var(--font-mono);font-size:11px;letter-spacing:0.14em;text-transform:uppercase;color:var(--w-muted-2);margin-bottom:12px;">
            Analysis #{{ $analysis }}
        </div>
        <p style="font-size:14px;line-height:1.5;max-width:320px;margin:0 auto;">
            The Result screen is implemented in Phase 5. This is a placeholder so navigation works today.
        </p>
    </div>
</x-layouts.app>
