<div
    data-testid="history-page"
    style="display:flex;flex-direction:column;flex:1;padding:64px 0 24px;background:var(--w-cream);min-height:100vh;"
>
    {{-- Header --}}
    <div style="display:flex;justify-content:space-between;align-items:center;padding:0 22px;margin-bottom:18px;">
        <h1 style="font-family:var(--font-display);font-weight:400;font-size:28px;letter-spacing:-0.01em;color:var(--w-ink);margin:0;">History</h1>
        <button
            type="button"
            wire:click="refresh"
            data-testid="history-refresh"
            aria-label="Pull to refresh"
            style="appearance:none;background:transparent;border:0.5px solid var(--w-line-2);border-radius:999px;padding:6px 12px;font-family:var(--font-mono);font-size:10px;color:var(--w-muted);letter-spacing:0.08em;cursor:pointer;"
        >{{ $refreshing ? 'Refreshing…' : 'Refresh' }}</button>
    </div>

    {{-- Toast --}}
    @if ($toast)
        <div
            role="status"
            data-testid="history-toast"
            style="margin:0 22px 14px;padding:10px 14px;border-radius:12px;background:var(--w-paper);border:0.5px solid var(--w-line-2);font-family:var(--font-ui);font-size:13px;color:var(--w-ink);display:flex;align-items:center;justify-content:space-between;gap:12px;"
        >
            <span>{{ $toast }}</span>
            <button
                type="button"
                wire:click="clearToast"
                aria-label="Dismiss"
                style="appearance:none;background:transparent;border:0;color:var(--w-muted);cursor:pointer;font-family:var(--font-mono);font-size:11px;"
            >&times;</button>
        </div>
    @endif

    {{-- Filter chips --}}
    <div
        data-testid="history-filters"
        style="display:flex;gap:8px;padding:0 22px 16px;overflow-x:auto;overflow-y:hidden;scrollbar-width:none;"
    >
        @foreach ($filterChips as $chip)
            @php
                $isActive = $filter === $chip['key'];
            @endphp
            <button
                type="button"
                wire:click="setFilter(@js($chip['key']))"
                data-testid="history-filter"
                data-filter-key="{{ $chip['key'] }}"
                aria-pressed="{{ $isActive ? 'true' : 'false' }}"
                style="appearance:none;flex-shrink:0;background:{{ $isActive ? 'var(--w-ink)' : 'transparent' }};color:{{ $isActive ? '#FAF8F2' : 'var(--w-ink-2)' }};border:0.5px solid {{ $isActive ? 'var(--w-ink)' : 'var(--w-line-2)' }};border-radius:999px;padding:8px 14px;font-family:var(--font-ui);font-size:13px;cursor:pointer;white-space:nowrap;"
            >{{ $chip['label'] }}</button>
        @endforeach
    </div>

    {{-- Loading state for first page --}}
    @if ($loadingPage1)
        <div
            data-testid="history-loading"
            style="padding:24px 22px;display:flex;justify-content:center;align-items:center;"
        >
            <span style="font-family:var(--font-mono);font-size:11px;color:var(--w-muted);letter-spacing:0.08em;">Loading…</span>
        </div>
    @endif

    {{-- Initial empty state --}}
    @if ($showInitialEmpty)
        <div
            data-testid="history-empty"
            style="padding:48px 22px;display:flex;flex-direction:column;align-items:center;gap:14px;text-align:center;"
        >
            <h2 style="font-family:var(--font-display);font-weight:400;font-size:24px;color:var(--w-ink);margin:0;">Nothing here yet.</h2>
            <p style="font-family:var(--font-ui);font-size:14px;line-height:1.5;color:var(--w-muted);margin:0;max-width:280px;">
                Send a product photo or type a product name to get your first verdict.
            </p>
            <div style="margin-top:8px;width:100%;max-width:280px;">
                <x-ui.button
                    data-testid="history-empty-cta"
                    wire:click="startNewAnalysis"
                >Start an analysis</x-ui.button>
            </div>
        </div>
    @endif

    {{-- Filtered empty state --}}
    @if ($showFilteredEmpty)
        <div
            data-testid="history-filtered-empty"
            style="padding:32px 22px;text-align:center;"
        >
            <p style="font-family:var(--font-ui);font-size:14px;color:var(--w-muted);margin:0;">{{ $filteredEmptyLabel }}</p>
        </div>
    @endif

    {{-- Grouped list --}}
    @if (! $loadingPage1 && ! $showInitialEmpty && ! $showFilteredEmpty)
        <div
            data-testid="history-list"
            style="display:flex;flex-direction:column;gap:18px;"
        >
            @foreach ($groupLabels as $group)
                @php
                    $groupRows = $grouped[$group['key']] ?? [];
                @endphp
                @if (count($groupRows) > 0)
                    <div data-testid="history-group" data-group-key="{{ $group['key'] }}">
                        <div style="padding:0 22px 8px;">
                            <x-ui.section-label>{{ $group['label'] }}</x-ui.section-label>
                        </div>
                        <div style="padding:0 18px;display:flex;flex-direction:column;gap:8px;">
                            @foreach ($groupRows as $row)
                                <div
                                    data-testid="history-row"
                                    data-analysis-id="{{ $row['id'] }}"
                                    data-verdict-bucket="{{ $row['verdict_bucket'] ?? '' }}"
                                    style="position:relative;background:var(--w-paper);border:0.5px solid var(--w-line);border-radius:14px;padding:12px;display:flex;gap:12px;align-items:center;"
                                >
                                    <x-ui.product-image :brand="$row['product_name']" :size="48" :radius="10" />
                                    <button
                                        type="button"
                                        wire:click="openAnalysis({{ (int) $row['id'] }})"
                                        @disabled($openingAnalysisId === $row['id'])
                                        data-testid="history-row-open"
                                        style="appearance:none;flex:1;min-width:0;text-align:left;background:transparent;border:0;cursor:pointer;padding:0;"
                                    >
                                        <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px;">
                                            @if ($row['verdict'])
                                                <x-ui.verdict-pill :verdict="$row['verdict']" size="sm" />
                                            @endif
                                            <span style="font-family:var(--font-mono);font-size:10px;color:var(--w-muted-2);letter-spacing:0.06em;text-transform:uppercase;">{{ $row['input_type'] === 'image' ? 'IMG' : 'TXT' }}</span>
                                            @if ($row['relative'])
                                                <span style="font-family:var(--font-mono);font-size:10px;color:var(--w-muted-2);letter-spacing:0.06em;">{{ $row['relative'] }}</span>
                                            @endif
                                            @if ($openingAnalysisId === $row['id'])
                                                <span
                                                    data-testid="history-row-loading"
                                                    style="font-family:var(--font-mono);font-size:10px;color:var(--w-muted-2);letter-spacing:0.08em;"
                                                >Loading…</span>
                                            @endif
                                        </div>
                                        <div style="font-size:14px;font-weight:500;color:var(--w-ink);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                            {{ $row['product_name'] }}
                                        </div>
                                    </button>
                                    <button
                                        type="button"
                                        wire:click="requestDelete({{ (int) $row['id'] }})"
                                        data-testid="history-row-delete"
                                        aria-label="Delete analysis"
                                        style="appearance:none;background:transparent;border:0;padding:6px;cursor:pointer;color:var(--w-muted-2);"
                                    >
                                        <x-ui.icon name="close" :size="16" />
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endforeach
        </div>

        {{-- Load more (infinite scroll trigger) --}}
        @if ($hasNextPage)
            <div
                data-testid="history-load-more"
                style="padding:24px 22px;display:flex;justify-content:center;"
            >
                <button
                    type="button"
                    wire:click="loadMore"
                    @disabled($loadingMore)
                    data-testid="history-load-more-button"
                    style="appearance:none;background:transparent;border:0.5px solid var(--w-line-2);border-radius:999px;padding:10px 18px;font-family:var(--font-ui);font-size:13px;color:var(--w-ink-2);cursor:pointer;"
                >{{ $loadingMore ? 'Loading…' : 'Load more' }}</button>
            </div>
        @endif
    @endif

    {{-- Delete confirmation --}}
    @if ($confirmDeleteId !== null)
        <div
            role="dialog"
            aria-modal="true"
            data-testid="history-delete-confirm"
            style="position:fixed;inset:0;background:rgba(20,19,15,0.45);display:flex;align-items:flex-end;justify-content:center;z-index:60;padding:18px;"
        >
            <div style="background:var(--w-paper);border-radius:18px;padding:20px;width:100%;max-width:420px;display:flex;flex-direction:column;gap:14px;">
                <div>
                    <h3 style="font-family:var(--font-display);font-size:18px;color:var(--w-ink);margin:0 0 6px;">Delete this analysis?</h3>
                    <p style="font-family:var(--font-ui);font-size:13px;color:var(--w-muted);margin:0;">This cannot be undone.</p>
                </div>
                <div style="display:flex;gap:10px;">
                    <button
                        type="button"
                        wire:click="cancelDelete"
                        data-testid="history-delete-cancel"
                        style="appearance:none;flex:1;background:transparent;border:0.5px solid var(--w-line-2);border-radius:14px;height:48px;font-family:var(--font-ui);font-size:15px;color:var(--w-ink);cursor:pointer;"
                    >Cancel</button>
                    <button
                        type="button"
                        wire:click="confirmDelete"
                        @disabled($deletingAnalysisId === $confirmDeleteId)
                        data-testid="history-delete-confirm-button"
                        style="appearance:none;flex:1;background:var(--w-skip);color:#FAF8F2;border:0;border-radius:14px;height:48px;font-family:var(--font-ui);font-size:15px;cursor:pointer;"
                    >{{ $deletingAnalysisId === $confirmDeleteId ? 'Deleting…' : 'Delete' }}</button>
                </div>
            </div>
        </div>
    @endif
</div>
