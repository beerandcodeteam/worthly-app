<?php

use App\Support\Verdict;

it('maps every API decision to the correct verdict bucket', function (string $decision, Verdict $expected) {
    expect(Verdict::fromApiDecision($decision))->toBe($expected);
})->with([
    'buy decision' => ['buy', Verdict::Buy],
    'buy_if_price_is_good decision' => ['buy_if_price_is_good', Verdict::Buy],
    'wait decision' => ['wait', Verdict::Wait],
    'consider_alternatives decision' => ['consider_alternatives', Verdict::Wait],
    'do_not_buy decision' => ['do_not_buy', Verdict::Skip],
]);

it('exposes color, label, and code tokens for each bucket', function () {
    expect(Verdict::Buy->label())->toBe('Buy')
        ->and(Verdict::Buy->code())->toBe('BUY')
        ->and(Verdict::Buy->color())->toBe('#1B7A3F');

    expect(Verdict::Wait->label())->toBe('Wait')
        ->and(Verdict::Wait->code())->toBe('WAIT')
        ->and(Verdict::Wait->color())->toBe('#B26D12');

    expect(Verdict::Skip->label())->toBe('Skip')
        ->and(Verdict::Skip->code())->toBe('SKIP')
        ->and(Verdict::Skip->color())->toBe('#A8392C');
});
