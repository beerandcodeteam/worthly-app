<?php

use App\Support\ProsConsSplitter;

it('splits sentences into pros and cons based on connector keywords', function () {
    $splitter = new ProsConsSplitter;

    $text = 'The MX Master 3S is a premium wireless mouse with excellent ergonomics, but it is expensive compared to other options. The silent clicks are great. However, the battery life is disappointing.';

    $result = $splitter->split($text);

    expect($result['fallback'])->toBeNull();
    expect($result['pros'])->not->toBeEmpty();
    expect($result['cons'])->not->toBeEmpty();

    $prosJoined = implode(' ', $result['pros']);
    $consJoined = implode(' ', $result['cons']);

    expect($prosJoined)->toContain('excellent ergonomics');
    expect($prosJoined)->toContain('silent clicks');

    expect($consJoined)->toContain('expensive');
    expect($consJoined)->toContain('battery life is disappointing');
});

it('returns a single paragraph fallback when splitting fails', function () {
    $splitter = new ProsConsSplitter;

    $neutralText = 'The product was released in 2023 and ships in three colors.';

    $result = $splitter->split($neutralText);

    expect($result['fallback'])->toBe($neutralText);
    expect($result['pros'])->toBe([]);
    expect($result['cons'])->toBe([]);
});

it('returns an empty result when input is null or empty', function () {
    $splitter = new ProsConsSplitter;

    expect($splitter->split(null))->toBe(['pros' => [], 'cons' => [], 'fallback' => null]);
    expect($splitter->split(''))->toBe(['pros' => [], 'cons' => [], 'fallback' => null]);
});
