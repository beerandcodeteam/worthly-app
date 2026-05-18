@props([
    'image' => null,
    'query' => null,
    'pipelineSteps' => [],
    'analysisStatus' => '',
    'analysisFailed' => false,
    'lastError' => null,
    'autoSubmit' => false,
    'autoSubmitMethod' => null,
    'pollMethod' => 'pollAnalysisStatus',
])

@php
    use App\Support\AnalysisPipeline;

    $isFailed = $analysisFailed || $analysisStatus === AnalysisPipeline::STATUS_FAILED;
    $isPolling = ! $isFailed && ! AnalysisPipeline::isTerminal($analysisStatus);
    $eyebrow = $image ? 'Image analysis' : 'Text analysis';
@endphp

<div
    data-testid="analyzing-loader"
    data-analysis-status="{{ $analysisStatus }}"
    @if ($autoSubmit && $autoSubmitMethod) wire:init="{{ $autoSubmitMethod }}" @endif
    @if ($isPolling) wire:poll.1s="{{ $pollMethod }}" @endif
    style="display:flex;flex-direction:column;flex:1;padding:0 0 28px;background:var(--w-cream);"
>
    <x-ui.screen-header
        transparent
        :eyebrow="$eyebrow"
        :title="$isFailed ? 'Analysis failed' : 'Worthly is thinking…'"
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
            @elseif ($query !== null && trim((string) $query) !== '')
                <div
                    data-testid="loader-text-echo"
                    style="margin-bottom:28px;padding:14px;background:var(--w-paper);border-radius:14px;border:0.5px solid var(--w-line);"
                >
                    <div style="font-family:var(--font-mono);font-size:10px;color:var(--w-muted);letter-spacing:0.12em;text-transform:uppercase;margin-bottom:6px;">Your question</div>
                    <div style="font-size:14px;line-height:1.5;color:var(--w-ink);">"{{ $query }}"</div>
                </div>
            @endif

            <div data-testid="loader-steps" style="display:flex;flex-direction:column;gap:2px;">
                @foreach ($pipelineSteps as $i => $step)
                    @php
                        $state = $step['state'];
                        if ($isFailed && $state === 'active') {
                            $state = 'failed';
                        }

                        $rowOpacity = $state === 'idle' ? '0.4' : '1';
                        $labelColor = match ($state) {
                            'active' => 'var(--w-ink)',
                            'failed' => 'var(--w-skip)',
                            default => 'var(--w-ink-2)',
                        };
                        $labelWeight = $state === 'active' ? '500' : '400';
                    @endphp
                    <div
                        data-testid="loader-step"
                        data-step-index="{{ $i }}"
                        data-step-key="{{ $step['key'] }}"
                        data-step-state="{{ $state }}"
                        style="display:flex;align-items:center;gap:14px;padding:14px 4px;border-bottom:0.5px solid var(--w-line);opacity:{{ $rowOpacity }};transition:opacity 200ms;"
                    >
                        <div style="width:22px;height:22px;position:relative;flex-shrink:0;">
                            @switch($state)
                                @case('done')
                                    <div style="width:22px;height:22px;border-radius:50%;background:var(--w-buy);color:#FAF8F2;display:flex;align-items:center;justify-content:center;">
                                        <x-ui.icon name="check" :size="12" color="#FAF8F2" />
                                    </div>
                                    @break

                                @case('active')
                                    <div
                                        aria-hidden="true"
                                        class="worthly-step-spinner"
                                        style="width:22px;height:22px;border-radius:50%;border:1.5px solid var(--w-line-2);border-top-color:var(--w-ink);"
                                    ></div>
                                    @break

                                @case('failed')
                                    <div style="width:22px;height:22px;border-radius:50%;background:var(--w-skip);color:#FAF8F2;display:flex;align-items:center;justify-content:center;font-family:var(--font-mono);font-size:11px;font-weight:700;">!</div>
                                    @break

                                @default
                                    <div style="width:22px;height:22px;border-radius:50%;border:1px solid var(--w-line-2);"></div>
                            @endswitch
                        </div>
                        <div style="flex:1;font-size:14px;color:{{ $labelColor }};font-weight:{{ $labelWeight }};">{{ $step['label'] }}</div>
                        <span style="font-family:var(--font-mono);font-size:10px;color:var(--w-muted);letter-spacing:0.08em;">
                            {{ str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) }}/{{ str_pad((string) count($pipelineSteps), 2, '0', STR_PAD_LEFT) }}
                        </span>
                    </div>
                @endforeach
            </div>

            @if ($isFailed)
                <div
                    role="alert"
                    data-testid="analysis-failed"
                    style="margin-top:20px;padding:14px;background:var(--w-skip-soft);border:0.5px solid var(--w-skip);border-radius:12px;color:var(--w-skip);font-family:var(--font-ui);font-size:13px;line-height:1.5;"
                >
                    {{ $lastError ?: "Something went wrong while running this analysis. Please try again." }}
                </div>

                <div style="display:flex;flex-direction:column;gap:8px;margin-top:16px;">
                    <button
                        type="button"
                        wire:click="retryAnalysis"
                        data-testid="analysis-retry"
                        style="appearance:none;border:0;background:var(--w-ink);color:#FAF8F2;font-family:var(--font-ui);font-size:15px;font-weight:500;height:48px;border-radius:14px;cursor:pointer;"
                    >Try again</button>
                </div>
            @endif
        </div>

        @if (! $isFailed)
            <div style="margin-top:auto;text-align:center;font-family:var(--font-mono);font-size:10px;color:var(--w-muted-2);letter-spacing:0.1em;text-transform:uppercase;padding-top:24px;">
                @switch($analysisStatus)
                    @case(AnalysisPipeline::STATUS_PENDING)
                        Queued — starting shortly
                        @break
                    @case(AnalysisPipeline::STATUS_COMPLETED)
                        Finishing up
                        @break
                    @default
                        Model · web search enabled
                @endswitch
            </div>
        @endif
    </div>

    @once
        <style>
            @keyframes worthly-step-spin {
                from { transform: rotate(0); }
                to   { transform: rotate(360deg); }
            }
            .worthly-step-spinner { animation: worthly-step-spin 700ms linear infinite; }
            @media (prefers-reduced-motion: reduce) {
                .worthly-step-spinner { animation: none !important; }
            }
        </style>
    @endonce
</div>
