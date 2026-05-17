<div
    data-testid="analyze-screen"
    style="display:flex;flex-direction:column;flex:1;background:var(--w-cream);min-height:100vh;"
>
    @if ($upstreamError)
        {{-- 502 upstream failure screen --}}
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
        {{-- Analyzing loader (US-3.3) --}}
        <div
            data-testid="analyzing-loader"
            style="display:flex;flex-direction:column;flex:1;padding:0 0 28px;background:var(--w-cream);"
        >
            <x-ui.screen-header
                transparent
                :eyebrow="$image ? 'Image analysis' : 'Text analysis'"
                title="Worthly is thinking…"
            />

            <div style="flex:1;padding:0 28px;display:flex;flex-direction:column;">
                <div style="margin-top:24px;padding:0 4px;">
                    @if ($image)
                        <div
                            data-testid="loader-image-echo"
                            style="margin-bottom:28px;padding:16px;background:var(--w-paper);border-radius:14px;border:0.5px solid var(--w-line);display:flex;gap:12px;align-items:center;"
                        >
                            <img
                                src="{{ $image->temporaryUrl() }}"
                                alt="Selected image"
                                data-testid="loader-image-thumb"
                                style="width:72px;height:72px;border-radius:10px;object-fit:cover;"
                            />
                            <div>
                                <div style="font-family:var(--font-mono);font-size:10px;color:var(--w-muted);letter-spacing:0.12em;text-transform:uppercase;margin-bottom:4px;">Image uploaded</div>
                                <div style="font-size:14px;color:var(--w-ink);">{{ $image->getClientOriginalName() }}</div>
                            </div>
                        </div>
                    @elseif (trim($query) !== '')
                        <div
                            data-testid="loader-text-echo"
                            style="margin-bottom:28px;padding:14px;background:var(--w-paper);border-radius:14px;border:0.5px solid var(--w-line);"
                        >
                            <div style="font-family:var(--font-mono);font-size:10px;color:var(--w-muted);letter-spacing:0.12em;text-transform:uppercase;margin-bottom:6px;">Your question</div>
                            <div style="font-size:14px;line-height:1.5;color:var(--w-ink);">"{{ $query }}"</div>
                        </div>
                    @endif

                    <div
                        data-testid="loader-steps"
                        x-data="{ step: 0, total: {{ count($steps) }} }"
                        x-init="
                            const tick = () => {
                                if (step < total - 1) {
                                    step++;
                                    setTimeout(tick, 700);
                                }
                            };
                            setTimeout(tick, 700);
                        "
                        style="display:flex;flex-direction:column;gap:2px;"
                    >
                        @foreach ($steps as $i => $label)
                            <div
                                data-testid="loader-step"
                                data-step-index="{{ $i }}"
                                style="display:flex;align-items:center;gap:14px;padding:14px 4px;border-bottom:0.5px solid var(--w-line);"
                            >
                                <div style="width:22px;height:22px;border-radius:50%;border:1px solid var(--w-line-2);"
                                     :class="step > {{ $i }} ? 'is-done' : (step === {{ $i }} ? 'is-active' : 'is-idle')"
                                ></div>
                                <div style="flex:1;font-size:14px;color:var(--w-ink-2);">{{ $label }}</div>
                                <span style="font-family:var(--font-mono);font-size:10px;color:var(--w-muted);letter-spacing:0.08em;">
                                    {{ str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) }}/{{ str_pad((string) count($steps), 2, '0', STR_PAD_LEFT) }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div style="margin-top:auto;text-align:center;font-family:var(--font-mono);font-size:10px;color:var(--w-muted-2);letter-spacing:0.1em;text-transform:uppercase;">
                    Model + web search running
                </div>
            </div>
        </div>
    @else
        {{-- Composer --}}
        <div style="padding:62px 0 24px;">
            <x-ui.screen-header transparent title="New analysis" />

            <div style="padding:18px 18px 0;">
                <div
                    data-testid="composer"
                    style="background:var(--w-paper);border:0.5px solid var(--w-line-2);border-radius:18px;padding:14px 14px 12px;"
                >
                    <textarea
                        wire:model.live="query"
                        data-testid="composer-input"
                        placeholder="Ask Worthly about any product…"
                        rows="3"
                        maxlength="{{ \App\Livewire\Analyze\Composer::MAX_QUERY_LENGTH }}"
                        style="width:100%;border:0;outline:0;background:transparent;resize:none;font-family:var(--font-ui);font-size:16px;line-height:1.4;color:var(--w-ink);min-height:60px;"
                    >{{ $query }}</textarea>

                    @error('query')
                        <div
                            data-testid="composer-error-query"
                            role="alert"
                            style="margin-top:6px;font-family:var(--font-ui);font-size:12px;color:var(--w-skip,#B33A3A);"
                        >{{ $message }}</div>
                    @enderror

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

                        @error('image')
                            <div
                                data-testid="composer-error-image"
                                role="alert"
                                style="margin-top:8px;font-family:var(--font-ui);font-size:12px;color:var(--w-skip,#B33A3A);"
                            >{{ $message }}</div>
                        @enderror
                    @else
                        @error('image')
                            <div
                                data-testid="composer-error-image"
                                role="alert"
                                style="margin-top:8px;font-family:var(--font-ui);font-size:12px;color:var(--w-skip,#B33A3A);"
                            >{{ $message }}</div>
                        @enderror
                    @endif

                    @if ($this->hasBothInputs())
                        <div
                            data-testid="composer-hint-both"
                            style="margin-top:8px;font-family:var(--font-ui);font-size:12px;color:var(--w-muted);"
                        >Remove either the text or the image to continue.</div>
                    @endif

                    <div style="display:flex;align-items:center;justify-content:space-between;margin-top:10px;">
                        <div style="display:flex;gap:6px;">
                            <label
                                for="composer-image-input"
                                data-testid="composer-camera"
                                style="appearance:none;border:0.5px solid var(--w-line);background:transparent;border-radius:999px;height:32px;width:32px;display:flex;align-items:center;justify-content:center;cursor:pointer;color:var(--w-ink-2);"
                                aria-label="Add image"
                            >
                                <x-ui.icon name="camera" :size="16" />
                            </label>
                            <input
                                id="composer-image-input"
                                type="file"
                                wire:model="image"
                                data-testid="composer-image-input"
                                accept="image/jpeg,image/png,image/webp"
                                style="display:none;"
                            />
                        </div>

                        @php $canSubmit = $this->canSubmit(); @endphp
                        <button
                            type="button"
                            wire:click="submit"
                            data-testid="composer-ask"
                            @disabled(! $canSubmit)
                            aria-disabled="{{ $canSubmit ? 'false' : 'true' }}"
                            style="appearance:none;border:0;width:36px;height:36px;border-radius:999px;display:flex;align-items:center;justify-content:center;background:{{ $canSubmit ? 'var(--w-ink)' : 'var(--w-line-2)' }};color:{{ $canSubmit ? '#FAF8F2' : 'var(--w-muted)' }};cursor:{{ $canSubmit ? 'pointer' : 'not-allowed' }};"
                            aria-label="Ask"
                        >
                            <x-ui.icon name="arrow-right" :size="16" />
                        </button>
                    </div>
                </div>

                <div style="margin-top:12px;display:flex;justify-content:flex-end;">
                    <span
                        data-testid="composer-char-count"
                        style="font-family:var(--font-mono);font-size:10px;color:var(--w-muted-2);letter-spacing:0.08em;"
                    >{{ mb_strlen($query) }}/{{ \App\Livewire\Analyze\Composer::MAX_QUERY_LENGTH }}</span>
                </div>
            </div>
        </div>
    @endif
</div>
