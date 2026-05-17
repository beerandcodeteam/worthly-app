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
     * @return list<array{eyebrow: string, headline: string, body: string}>
     */
    public function slides(): array
    {
        return [
            [
                'eyebrow' => '01 / Worthly',
                'headline' => 'Is it actually worth it?',
                'body' => "Snap a photo or paste a product name and Worthly tells you if it's a good buy.",
            ],
            [
                'eyebrow' => '02 / What you get',
                'headline' => 'A friendly second opinion.',
                'body' => 'Friendly second opinion that reads every review for you.',
            ],
            [
                'eyebrow' => '03 / How it works',
                'headline' => 'Buy. Wait. Skip.',
                'body' => 'Three clear verdicts: Buy, Wait, Skip.',
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
