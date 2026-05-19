<?php

use App\Contracts\SecureTokenStorage;
use App\Livewire\Onboarding\Carousel;
use Livewire\Livewire;

beforeEach(function () {
    fakeWorthlyApi();
    app(SecureTokenStorage::class)->forget();
});

it('renders three slides with the correct copy', function () {
    Livewire::test(Carousel::class)
        ->assertSet('currentSlide', 0)
        ->assertSeeText("Snap a photo or paste a product name. Worthly tells you if it's a good buy — right now.")
        ->call('next')
        ->assertSet('currentSlide', 1)
        ->assertSeeText("Like asking the friend who reads every review so you don't have to. Honest. Specific. Yours.")
        ->call('next')
        ->assertSet('currentSlide', 2)
        ->assertSeeText('Three verdicts. One clear recommendation per product, backed by fresh prices and real reviews.');
});

it('routes Get started to Register and the secondary CTAs to Login when no token is stored', function () {
    Livewire::test(Carousel::class)
        ->call('goTo', 2)
        ->call('getStarted')
        ->assertRedirect(route('register'));

    Livewire::test(Carousel::class)
        ->call('goTo', 2)
        ->call('iHaveAnAccount')
        ->assertRedirect(route('login'));

    Livewire::test(Carousel::class)
        ->call('skip')
        ->assertRedirect(route('login'));
});

it('routes every CTA to Home when a token is already stored', function () {
    app(SecureTokenStorage::class)->put('valid-token');

    Livewire::test(Carousel::class)
        ->call('getStarted')
        ->assertRedirect(route('home'));

    Livewire::test(Carousel::class)
        ->call('iHaveAnAccount')
        ->assertRedirect(route('home'));

    Livewire::test(Carousel::class)
        ->call('skip')
        ->assertRedirect(route('home'));
});

it('shows onboarding on every cold start regardless of prior completion', function () {
    $this->get(route('onboarding'))->assertOk();

    app(SecureTokenStorage::class)->put('valid-token');

    $this->get(route('onboarding'))->assertOk();
});
