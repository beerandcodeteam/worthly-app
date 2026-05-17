<?php

use App\Contracts\SecureTokenStorage;
use App\Livewire\Profile\ProfilePage;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

beforeEach(function () {
    config()->set('services.worthly.base_url', 'https://api.worthly.test');
    fakeWorthlyApi();
    app(SecureTokenStorage::class)->put('usage-test-token');
});

function fakeUsageEndpoints(int $total): void
{
    Http::fake([
        'api.worthly.test/api/me' => Http::response([
            'data' => ['name' => 'Ada Lovelace', 'email' => 'ada@example.com'],
        ], 200),
        'api.worthly.test/api/analyses?page=1' => Http::response([
            'data' => [],
            'meta' => ['total' => $total, 'per_page' => 15, 'current_page' => 1],
            'links' => ['next' => null, 'prev' => null],
        ], 200),
    ]);
}

it('shows Total analyses from meta.total of /api/analyses page 1', function () {
    fakeUsageEndpoints(total: 42);

    Livewire::test(ProfilePage::class)
        ->assertSet('totalAnalyses', 42)
        ->assertSeeHtml('data-testid="stat-total-analyses"')
        ->assertSeeText('42');

    Http::assertSent(fn ($request) => str_starts_with($request->url(), 'https://api.worthly.test/api/analyses')
        && $request['page'] === 1);
});

it('shows 0 for Saved products and em-dash for Money saved', function () {
    fakeUsageEndpoints(total: 3);

    $component = Livewire::test(ProfilePage::class);

    expect($component->instance()->savedProducts())->toBe(0);
    expect($component->instance()->moneySaved())->toBe('—');

    $component
        ->assertSeeHtml('data-testid="stat-saved-products"')
        ->assertSeeHtml('data-testid="stat-money-saved"');

    $html = $component->html();

    expect($html)->toContain('data-testid="stat-saved-products"');
    expect($html)->toMatch('/data-testid="stat-saved-products"[^>]*>0</');
    expect($html)->toMatch('/data-testid="stat-money-saved"[^>]*>—</');
});

it('disables the Upgrade CTA with the Coming soon tooltip', function () {
    fakeUsageEndpoints(total: 3);

    $component = Livewire::test(ProfilePage::class);

    expect($component->instance()->upgradeCtaDisabled())->toBeTrue();
    expect($component->instance()->upgradeTooltip())->toBe('Coming soon');

    $component
        ->assertSeeHtml('data-testid="upgrade-cta"')
        ->assertSeeHtml('aria-disabled="true"')
        ->assertSeeHtml('title="Coming soon"');

    $html = $component->html();
    expect($html)->toMatch('/data-testid="upgrade-cta"[^>]*\bdisabled\b/');
});

it('never blocks a submission based on the usage indicator', function () {
    fakeUsageEndpoints(total: 999);

    $component = Livewire::test(ProfilePage::class)
        ->assertSet('totalAnalyses', 999);

    expect($component->instance()->submissionsBlocked())->toBeFalse();
    expect($component->get('totalAnalyses'))->toBeGreaterThanOrEqual(ProfilePage::FREE_PLAN_QUOTA);

    $html = $component->html();
    expect($html)->not->toContain('Quota exceeded');
    expect($html)->not->toContain('Limit reached');
    expect($html)->not->toContain('aria-disabled="true" data-testid="composer-ask"');
});
