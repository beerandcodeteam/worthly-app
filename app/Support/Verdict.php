<?php

namespace App\Support;

enum Verdict: string
{
    case Buy = 'buy';
    case Wait = 'wait';
    case Skip = 'skip';

    public static function fromApiDecision(string $decision): self
    {
        return match ($decision) {
            'buy', 'buy_if_price_is_good' => self::Buy,
            'wait', 'consider_alternatives' => self::Wait,
            'do_not_buy' => self::Skip,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Buy => 'Buy',
            self::Wait => 'Wait',
            self::Skip => 'Skip',
        };
    }

    public function code(): string
    {
        return match ($this) {
            self::Buy => 'BUY',
            self::Wait => 'WAIT',
            self::Skip => 'SKIP',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Buy => '#1B7A3F',
            self::Wait => '#B26D12',
            self::Skip => '#A8392C',
        };
    }
}
