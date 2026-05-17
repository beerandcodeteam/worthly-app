<?php

namespace App\Support;

class ProsConsSplitter
{
    /**
     * @var list<string>
     */
    private const PRO_KEYWORDS = [
        'pro',
        'pros',
        'advantage',
        'advantages',
        'strength',
        'strengths',
        'benefit',
        'benefits',
        'great',
        'excellent',
        'good',
        'love',
        'loved',
        'reliable',
        'recommend',
        'recommended',
        'worth',
        'comfortable',
        'silent',
        'precise',
        'fast',
        'durable',
        'premium',
        'sleek',
        'efficient',
        'productive',
        'ergonomics',
        'ergonomic',
    ];

    /**
     * @var list<string>
     */
    private const CON_KEYWORDS = [
        'con',
        'cons',
        'disadvantage',
        'disadvantages',
        'drawback',
        'drawbacks',
        'downside',
        'downsides',
        'weakness',
        'weaknesses',
        'issue',
        'issues',
        'concern',
        'concerns',
        'complaint',
        'complaints',
        'expensive',
        'pricey',
        'overpriced',
        'lack',
        'lacks',
        'lacking',
        'missing',
        'noisy',
        'fragile',
        'slow',
        'short',
        'limited',
        'poor',
        'bad',
        'worse',
        'worst',
        'difficult',
        'uncomfortable',
        'disappointing',
        'fail',
        'fails',
        'failed',
        'problem',
        'problems',
        'flaw',
        'flaws',
    ];

    /**
     * @var list<string>
     */
    private const CONTRAST_CONNECTORS = [
        'but',
        'however',
        'although',
        'though',
        'whereas',
        'yet',
        'on the other hand',
        'nevertheless',
        'nonetheless',
        'unfortunately',
        'while',
    ];

    /**
     * Split a free-form cost/benefit paragraph into pros and cons.
     *
     * @return array{pros: list<string>, cons: list<string>, fallback: ?string}
     */
    public function split(?string $text): array
    {
        $text = is_string($text) ? trim($text) : '';

        if ($text === '') {
            return ['pros' => [], 'cons' => [], 'fallback' => null];
        }

        $sentences = $this->splitIntoSentences($text);

        $pros = [];
        $cons = [];
        $signalDetected = false;

        foreach ($sentences as $sentence) {
            [$head, $tail] = $this->splitOnContrast($sentence);

            $signalDetected = $this->classify($head, $pros, $cons, defaultIsCon: false) || $signalDetected;

            if ($tail !== null) {
                $signalDetected = $this->classify($tail, $pros, $cons, defaultIsCon: true) || $signalDetected;
                $signalDetected = true;
            }
        }

        if (! $signalDetected) {
            return ['pros' => [], 'cons' => [], 'fallback' => $text];
        }

        return [
            'pros' => array_values(array_unique($pros)),
            'cons' => array_values(array_unique($cons)),
            'fallback' => null,
        ];
    }

    /**
     * @return list<string>
     */
    private function splitIntoSentences(string $text): array
    {
        $parts = preg_split('/(?<=[.!?])\s+/u', $text) ?: [];

        return array_values(array_filter(array_map('trim', $parts), fn (string $s): bool => $s !== ''));
    }

    /**
     * @return array{0: string, 1: ?string}
     */
    private function splitOnContrast(string $sentence): array
    {
        $lower = strtolower($sentence);

        foreach (self::CONTRAST_CONNECTORS as $connector) {
            $pattern = '/\b'.preg_quote($connector, '/').'\b/u';

            if (! preg_match($pattern, $lower, $match, PREG_OFFSET_CAPTURE)) {
                continue;
            }

            $offset = (int) $match[0][1];

            if ($offset <= 0) {
                continue;
            }

            $head = trim(rtrim(substr($sentence, 0, $offset), ', '));
            $tail = trim(substr($sentence, $offset + strlen($connector)));
            $tail = ltrim($tail, ', ');

            if ($head !== '' && $tail !== '') {
                return [$head, $tail];
            }
        }

        return [$sentence, null];
    }

    /**
     * @param  list<string>  $pros
     * @param  list<string>  $cons
     * @return bool True if the sentence had a clear pro/con signal.
     */
    private function classify(string $sentence, array &$pros, array &$cons, bool $defaultIsCon): bool
    {
        $sentence = trim($sentence);

        if ($sentence === '') {
            return false;
        }

        $words = $this->wordsOf($sentence);
        $proHits = count(array_intersect($words, self::PRO_KEYWORDS));
        $conHits = count(array_intersect($words, self::CON_KEYWORDS));

        if ($conHits > $proHits) {
            $cons[] = $sentence;

            return true;
        }

        if ($proHits > $conHits) {
            $pros[] = $sentence;

            return true;
        }

        if ($defaultIsCon) {
            $cons[] = $sentence;
        } else {
            $pros[] = $sentence;
        }

        return false;
    }

    /**
     * @return list<string>
     */
    private function wordsOf(string $sentence): array
    {
        $clean = preg_replace('/[^\p{L}\p{N}\s]+/u', ' ', strtolower($sentence)) ?? '';
        $words = preg_split('/\s+/u', $clean) ?: [];

        return array_values(array_filter($words, fn (string $w): bool => $w !== ''));
    }
}
