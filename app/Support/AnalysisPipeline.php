<?php

namespace App\Support;

class AnalysisPipeline
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_PROCESSING = 'processing';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_FAILED = 'failed';

    /**
     * @var array<int, array{key: string, label: string}>
     */
    public const STEPS = [
        ['key' => 'l1', 'label' => 'Identifying product'],
        ['key' => 'l2', 'label' => 'Searching the web'],
        ['key' => 'l3', 'label' => 'Reading reviews'],
        ['key' => 'l4', 'label' => 'Comparing alternatives'],
        ['key' => 'l5', 'label' => 'Forming a verdict'],
    ];

    /**
     * @return list<string>
     */
    public static function stepLabels(): array
    {
        return array_map(fn (array $step): string => $step['label'], self::STEPS);
    }

    /**
     * Index of the given step key (l1..l5), or -1 when unknown.
     */
    public static function stepIndex(?string $stepKey): int
    {
        if ($stepKey === null) {
            return -1;
        }

        foreach (self::STEPS as $i => $step) {
            if ($step['key'] === $stepKey) {
                return $i;
            }
        }

        return -1;
    }

    /**
     * @return 'done'|'active'|'idle'
     */
    public static function stepState(int $stepIndex, string $status, ?string $currentStep): string
    {
        if ($status === self::STATUS_COMPLETED) {
            return 'done';
        }

        $currentIndex = self::stepIndex($currentStep);

        if ($status === self::STATUS_PENDING && $currentIndex === -1) {
            $currentIndex = 0;
        }

        if ($status === self::STATUS_FAILED) {
            if ($currentIndex !== -1 && $stepIndex < $currentIndex) {
                return 'done';
            }

            return $stepIndex === $currentIndex ? 'active' : 'idle';
        }

        if ($currentIndex === -1) {
            return 'idle';
        }

        if ($stepIndex < $currentIndex) {
            return 'done';
        }

        return $stepIndex === $currentIndex ? 'active' : 'idle';
    }

    public static function isTerminal(string $status): bool
    {
        return $status === self::STATUS_COMPLETED || $status === self::STATUS_FAILED;
    }
}
