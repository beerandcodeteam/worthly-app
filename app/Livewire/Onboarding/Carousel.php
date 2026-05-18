<?php

namespace App\Livewire\Onboarding;

use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

class Carousel extends Component
{
    public const FIRST_LAUNCH_FLAG = 'onboarding.completed';

    public int $currentSlide = 0;

    /**
     * @return list<array{eyebrow: string, headline_html: string, body: string, art: string}>
     */
    public function slides(): array
    {
        return [
            [
                'eyebrow' => '01 / Worthly',
                'headline_html' => 'Is it <em>actually</em> worth it?',
                'body' => "Snap a photo or paste a product name. Worthly tells you if it's a good buy — right now.",
                'art' => 'scan',
            ],
            [
                'eyebrow' => '02 / What you get',
                'headline_html' => 'A friendly second opinion.',
                'body' => "Like asking the friend who reads every review so you don't have to. Honest. Specific. Yours.",
                'art' => 'verdict',
            ],
            [
                'eyebrow' => '03 / How it works',
                'headline_html' => 'Buy. Wait. Skip.',
                'body' => 'Three verdicts. One clear recommendation per product, backed by fresh prices and real reviews.',
                'art' => 'trio',
            ],
        ];
    }

    public function mount(): mixed
    {
        if (Cache::get(self::FIRST_LAUNCH_FLAG) === true) {
            return $this->redirectRoute('login', navigate: false);
        }

        return null;
    }

    public function goTo(int $index): void
    {
        $max = count($this->slides()) - 1;
        $this->currentSlide = max(0, min($max, $index));
    }

    public function next(): void
    {
        $this->goTo($this->currentSlide + 1);
    }

    public function previous(): void
    {
        $this->goTo($this->currentSlide - 1);
    }

    public function skip(): mixed
    {
        $this->markCompleted();

        return $this->redirectRoute('login', navigate: true);
    }

    public function getStarted(): mixed
    {
        $this->markCompleted();

        return $this->redirectRoute('register', navigate: true);
    }

    public function iHaveAnAccount(): mixed
    {
        $this->markCompleted();

        return $this->redirectRoute('login', navigate: true);
    }

    #[Computed]
    public function isLastSlide(): bool
    {
        return $this->currentSlide === count($this->slides()) - 1;
    }

    private function markCompleted(): void
    {
        Cache::forever(self::FIRST_LAUNCH_FLAG, true);
    }

    #[Layout('components.layouts.guest')]
    #[Title('Welcome to Worthly')]
    public function render(): mixed
    {
        return view('livewire.onboarding.carousel', [
            'slides' => $this->slides(),
        ]);
    }
}
