<div
    data-testid="home-page"
    style="display:flex;flex-direction:column;flex:1;background:var(--w-cream);min-height:100vh;"
>
    @if ($upstreamError)
        {{-- 502 upstream failure --}}
        <div
            data-testid="upstream-error"
            style="display:flex;flex-direction:column;flex:1;padding:62px 22px 28px;background:var(--w-cream);"
        >
            <x-ui.screen-header transparent eyebrow="Something went wrong" title="Worthly is having trouble" />

            <div style="flex:1;display:flex;flex-direction:column;justify-content:center;gap:16px;">
                <p style="font-family:var(--font-ui);font-size:15px;line-height:1.5;color:var(--w-ink);margin:0;text-align:center;">
                    Worthly is having trouble right now. Your input is saved — try again in a moment.
                </p>
            </div>

            <div style="display:flex;flex-direction:column;gap:8px;margin-top:auto;">
                <button
                    type="button"
                    wire:click="submit"
                    data-testid="upstream-retry"
                    style="appearance:none;border:0;background:var(--w-ink);color:#FAF8F2;font-family:var(--font-ui);font-size:15px;font-weight:500;height:48px;border-radius:14px;cursor:pointer;"
                >Try again</button>
                <button
                    type="button"
                    wire:click="dismissUpstreamError"
                    data-testid="upstream-back"
                    style="appearance:none;border:0.5px solid var(--w-line);background:transparent;color:var(--w-ink);font-family:var(--font-ui);font-size:14px;height:44px;border-radius:14px;cursor:pointer;"
                >Back</button>
            </div>
        </div>
    @elseif ($submitting)
        @include('livewire._partials.analyzing-loader', [
            'image' => $image,
            'query' => null,
            'pipelineSteps' => $pipelineSteps,
            'analysisStatus' => $analysisStatus,
            'analysisFailed' => $analysisFailed,
            'lastError' => $lastError,
            'autoSubmit' => $autoSubmit,
            'autoSubmitMethod' => 'runImageAnalysis',
            'pollMethod' => 'pollAnalysisStatus',
        ])
    @else
        <div style="display:flex;flex-direction:column;flex:1;padding:64px 0 24px;">
            {{-- Header --}}
            <div style="display:flex;justify-content:space-between;align-items:center;padding:0 22px;margin-bottom:22px;">
                <x-ui.wordmark :size="22" />
                <div
                    data-testid="plan-usage"
                    style="display:flex;align-items:center;gap:6px;"
                >
                    <span style="font-family:var(--font-mono);font-size:10px;color:var(--w-muted);letter-spacing:0.1em;">{{ $planUsage }}</span>
                    <span
                        data-testid="plan-badge"
                        style="padding:4px 8px;background:var(--w-paper);border:0.5px solid var(--w-line);border-radius:999px;font-family:var(--font-mono);font-size:10px;font-weight:500;letter-spacing:0.08em;color:var(--w-ink);"
                    >FREE</span>
                </div>
            </div>

            {{-- Greeting --}}
            <div style="padding:0 22px;margin-bottom:24px;">
                <div
                    data-testid="home-greeting"
                    style="font-family:var(--font-mono);font-size:11px;font-weight:500;letter-spacing:0.14em;text-transform:uppercase;color:var(--w-muted);margin-bottom:10px;"
                >
                    @if ($this->firstName)
                        Hi, {{ $this->firstName }}
                    @else
                        Hi there
                    @endif
                </div>
                <h1 style="font-family:var(--font-display);font-weight:400;font-size:36px;line-height:1.05;letter-spacing:-0.01em;color:var(--w-ink);margin:0;">
                    Should you<br><span style="font-style:italic;">actually</span> buy it?
                </h1>
            </div>

            {{-- Toast --}}
            @if ($toast)
                <div
                    role="status"
                    data-testid="home-toast"
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

            {{-- Composer --}}
            <div style="padding:0 18px;margin-bottom:12px;">
                <form
                    wire:submit.prevent="submit"
                    data-testid="composer"
                    style="background:var(--w-paper);border:0.5px solid var(--w-line-2);border-radius:18px;padding:14px 14px 12px;"
                >
                    <textarea
                        wire:model.live="composer"
                        data-testid="composer-input"
                        placeholder="Ask Worthly about any product…"
                        rows="2"
                        maxlength="1000"
                        style="width:100%;border:0;outline:0;background:transparent;resize:none;font-family:var(--font-ui);font-size:16px;line-height:1.4;color:var(--w-ink);min-height:44px;"
                    >{{ $composer }}</textarea>

                    @if ($image)
                        <div
                            data-testid="composer-image-preview-wrap"
                            style="margin-top:12px;display:flex;align-items:center;gap:12px;"
                        >
                            <div
                                data-testid="composer-image-preview"
                                style="position:relative;width:64px;height:64px;border-radius:12px;overflow:hidden;background:var(--w-line-2);"
                            >
                                <img
                                    src="{{ $image->temporaryUrl() }}"
                                    alt="Preview"
                                    style="width:100%;height:100%;object-fit:cover;display:block;"
                                />
                                <button
                                    type="button"
                                    wire:click="removeImage"
                                    aria-label="Remove image"
                                    data-testid="composer-image-remove"
                                    style="position:absolute;top:-6px;right:-6px;width:22px;height:22px;border-radius:999px;background:var(--w-ink);color:#FAF8F2;border:0;font-family:var(--font-mono);font-size:12px;line-height:1;cursor:pointer;display:flex;align-items:center;justify-content:center;"
                                >&times;</button>
                            </div>
                            <div style="flex:1;font-family:var(--font-mono);font-size:11px;color:var(--w-muted);letter-spacing:0.06em;">
                                {{ $image->getClientOriginalName() }}
                            </div>
                        </div>
                    @endif

                    @error('image')
                        <div
                            data-testid="composer-error-image"
                            role="alert"
                            style="margin-top:8px;font-family:var(--font-ui);font-size:12px;color:var(--w-skip,#B33A3A);"
                        >{{ $message }}</div>
                    @enderror

                    @if ($this->hasBothInputs())
                        <div
                            data-testid="composer-hint-both"
                            style="margin-top:8px;font-family:var(--font-ui);font-size:12px;color:var(--w-muted);"
                        >Remove either the text or the image to continue.</div>
                    @endif

                    <div style="display:flex;align-items:center;justify-content:space-between;margin-top:10px;">
                        <div style="display:flex;gap:6px;">
                            <label
                                for="home-composer-image-input"
                                data-testid="composer-camera"
                                aria-label="Add image"
                                style="appearance:none;border:0.5px solid var(--w-line);background:transparent;border-radius:999px;height:32px;width:32px;display:flex;align-items:center;justify-content:center;cursor:pointer;color:var(--w-ink-2);"
                            >
                                <x-ui.icon name="camera" :size="16" />
                            </label>
                            <input
                                id="home-composer-image-input"
                                type="file"
                                wire:model="image"
                                data-testid="composer-image-input"
                                accept="image/jpeg,image/png,image/webp"
                                style="display:none;"
                            />
                        </div>

                        @php $canSubmit = $this->canSubmit(); @endphp
                        <button
                            type="submit"
                            data-testid="composer-ask"
                            @disabled(! $canSubmit)
                            aria-disabled="{{ $canSubmit ? 'false' : 'true' }}"
                            aria-label="Ask"
                            style="appearance:none;border:0;width:36px;height:36px;border-radius:999px;display:flex;align-items:center;justify-content:center;background:{{ $canSubmit ? 'var(--w-ink)' : 'var(--w-line-2)' }};color:{{ $canSubmit ? '#FAF8F2' : 'var(--w-muted)' }};cursor:{{ $canSubmit ? 'pointer' : 'not-allowed' }};"
                        >
                            <x-ui.icon name="arrow-right" :size="16" />
                        </button>
                    </div>
                </form>
            </div>

            {{-- Suggestion chips --}}
            <div style="padding:14px 22px 8px;">
                <x-ui.section-label>Try one</x-ui.section-label>
            </div>
            <div
                data-testid="suggestion-chips"
                style="display:flex;gap:8px;padding:0 22px 4px;overflow-x:auto;overflow-y:hidden;scrollbar-width:none;"
            >
                @foreach ($suggestions as $suggestion)
                    <button
                        type="button"
                        wire:click="prefillSuggestion(@js($suggestion))"
                        data-testid="suggestion-chip"
                        style="appearance:none;flex-shrink:0;background:transparent;border:0.5px solid var(--w-line-2);border-radius:999px;padding:8px 14px;font-family:var(--font-ui);font-size:13px;color:var(--w-ink-2);cursor:pointer;white-space:nowrap;"
                    >{{ $suggestion }}</button>
                @endforeach
                <div style="width:8px;flex-shrink:0;"></div>
            </div>

            {{-- Recent analyses --}}
            <div style="padding:24px 22px 8px;display:flex;justify-content:space-between;align-items:baseline;">
                <x-ui.section-label>Recent analyses</x-ui.section-label>
            </div>
            <div
                data-testid="recent-analyses"
                style="padding:6px 18px 0;display:flex;flex-direction:column;gap:8px;"
            >
                @forelse ($recentRows as $row)
                    <button
                        type="button"
                        wire:click="openAnalysis({{ (int) $row['id'] }})"
                        @disabled($openingAnalysisId === $row['id'])
                        data-testid="recent-card"
                        data-analysis-id="{{ $row['id'] }}"
                        style="appearance:none;background:var(--w-paper);border:0.5px solid var(--w-line);border-radius:14px;padding:12px;text-align:left;cursor:pointer;display:flex;gap:12px;align-items:center;width:100%;"
                    >
                        <x-ui.product-image :brand="$row['product_name']" :size="48" :radius="10" />
                        <div style="flex:1;min-width:0;">
                            <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px;">
                                @if ($row['verdict'])
                                    <x-ui.verdict-pill :verdict="$row['verdict']" size="sm" />
                                @endif
                                @if ($row['relative'])
                                    <span style="font-family:var(--font-mono);font-size:10px;color:var(--w-muted-2);letter-spacing:0.06em;">{{ $row['relative'] }}</span>
                                @endif
                                @if ($openingAnalysisId === $row['id'])
                                    <span
                                        data-testid="recent-card-loading"
                                        style="font-family:var(--font-mono);font-size:10px;color:var(--w-muted-2);letter-spacing:0.08em;"
                                    >Loading…</span>
                                @endif
                            </div>
                            <div style="font-size:14px;font-weight:500;color:var(--w-ink);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;margin-bottom:2px;">
                                {{ $row['product_name'] }}
                            </div>
                            @if ($row['summary'])
                                <div style="font-size:12px;color:var(--w-muted);line-height:1.4;display:-webkit-box;-webkit-line-clamp:1;-webkit-box-orient:vertical;overflow:hidden;">
                                    {{ $row['summary'] }}
                                </div>
                            @endif
                        </div>
                        <x-ui.icon name="chevron-right" :size="14" color="var(--w-muted-2)" />
                    </button>
                @empty
                    <div
                        data-testid="recent-empty"
                        style="padding:16px;border:0.5px dashed var(--w-line-2);border-radius:14px;font-family:var(--font-ui);font-size:13px;color:var(--w-muted);text-align:center;"
                    >
                        Send a product photo or type a product name to see your analyses here.
                    </div>
                @endforelse
            </div>
        </div>
    @endif
</div>
