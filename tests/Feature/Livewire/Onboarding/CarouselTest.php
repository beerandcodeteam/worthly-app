<?php

use App\Livewire\Onboarding\Carousel;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;

beforeEach(function () {
    Cache::forget(Carousel::FIRST_LAUNCH_FLAG);
});

it('renders three slides with the correct copy', function () {
    Livewire::test(Carousel::class)
        ->assertSet('currentSlide', 0)
        ->assertSeeText('Snap a photo or paste a product name and Worthly tells you if it\'s a good buy.')
        ->call('next')
        ->assertSet('currentSlide', 1)
        ->assertSeeText('Friendly second opinion that reads every review for you.')
        ->call('next')
        ->assertSet('currentSlide', 2)
        ->assertSeeText('Three clear verdicts: Buy, Wait, Skip.');
});

it('routes Get started to Register and the secondary CTA to Login', function () {
    Livewire::test(Carousel::class)
        ->call('goTo', 2)
        ->call('getStarted')
        ->assertRedirect(route('register'));

    Cache::forget(Carousel::FIRST_LAUNCH_FLAG);

    Livewire::test(Carousel::class)
        ->call('goTo', 2)
        ->call('iHaveAnAccount')
        ->assertRedirect(route('login'));
});

it('sets the first-launch flag once the user reaches the last slide or skips', function () {
    expect(Cache::get(Carousel::FIRST_LAUNCH_FLAG))->toBeNull();

    Livewire::test(Carousel::class)->call('skip');

    expect(Cache::get(Carousel::FIRST_LAUNCH_FLAG))->toBeTrue();

    Cache::forget(Carousel::FIRST_LAUNCH_FLAG);

    Livewire::test(Carousel::class)
        ->call('goTo', 2)
        ->call('getStarted');

    expect(Cache::get(Carousel::FIRST_LAUNCH_FLAG))->toBeTrue();
});

it('does not show onboarding again after the flag is set', function () {
    Cache::forever(Carousel::FIRST_LAUNCH_FLAG, true);

    $this->get(route('onboarding'))->assertRedirect(route('login'));
});
