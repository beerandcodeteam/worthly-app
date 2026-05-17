<?php

use App\Contracts\SecureTokenStorage;
use App\Livewire\History\HistoryPage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

beforeEach(function () {
    config()->set('services.worthly.base_url', 'https://api.worthly.test');
    fakeWorthlyApi();
    app(SecureTokenStorage::class)->put('delete-test-token');
    Carbon::setTestNow('2026-05-17 10:00:00');
});

afterEach(function () {
    Carbon::setTestNow();
});

function deleteHistoryListPayload(array $items): void
{
    Http::fake([
        'api.worthly.test/api/analyses?page=1' => Http::response([
            'data' => $items,
            'meta' => ['total' => count($items), 'per_page' => 15, 'current_page' => 1],
            'links' => ['next' => null, 'prev' => null],
        ], 200),
    ]);
}

function deletableRow(int $id, string $name = 'Item'): array
{
    return [
        'id' => $id,
        'product' => ['name' => $name, 'category' => 'Audio'],
        'recommendation' => ['decision' => 'buy', 'reason' => 'Reason'],
        'input_type' => 'text',
        'created_at' => '2026-05-17T09:00:00Z',
    ];
}

it('shows a confirmation before deleting', function () {
    deleteHistoryListPayload([
        deletableRow(1001, 'Confirm Product'),
    ]);

    $component = Livewire::test(HistoryPage::class)
        ->assertSeeText('Confirm Product')
        ->assertDontSeeHtml('data-testid="history-delete-confirm"');

    $component->call('requestDelete', 1001)
        ->assertSet('confirmDeleteId', 1001)
        ->assertSeeHtml('data-testid="history-delete-confirm"')
        ->assertSeeText('Delete this analysis?')
        ->assertSeeText('This cannot be undone.');

    // No DELETE request should have gone out before confirmation.
    Http::assertNotSent(fn ($request) => $request->method() === 'DELETE');

    $component->call('cancelDelete')
        ->assertSet('confirmDeleteId', null)
        ->assertDontSeeHtml('data-testid="history-delete-confirm"');
});

it('removes the row on 204 without re-fetching', function () {
    deleteHistoryListPayload([
        deletableRow(2001, 'Survives Product'),
        deletableRow(2002, 'Deleted Product'),
    ]);

    Http::fake([
        'api.worthly.test/api/analyses/2002' => Http::response('', 204),
    ]);

    Cache::put('analyses.2002', ['id' => 2002, 'cached' => true]);

    $component = Livewire::test(HistoryPage::class)
        ->assertSeeText('Survives Product')
        ->assertSeeText('Deleted Product');

    $listCallsBefore = collect(Http::recorded())
        ->filter(fn ($pair) => str_contains($pair[0]->url(), '/api/analyses?page=1'))
        ->count();

    $component->call('requestDelete', 2002)
        ->call('confirmDelete')
        ->assertSet('confirmDeleteId', null)
        ->assertSeeText('Survives Product')
        ->assertDontSeeText('Deleted Product');

    expect($component->get('rows'))->toHaveCount(1);
    expect($component->get('rows')[0]['id'])->toBe(2001);
    expect(Cache::get('analyses.2002'))->toBeNull();

    $deleteSent = Http::recorded(fn ($request) => $request->method() === 'DELETE'
        && $request->url() === 'https://api.worthly.test/api/analyses/2002');
    expect($deleteSent->count())->toBe(1);

    $listCallsAfter = collect(Http::recorded())
        ->filter(fn ($pair) => str_contains($pair[0]->url(), '/api/analyses?page=1'))
        ->count();

    expect($listCallsAfter)->toBe($listCallsBefore);
});

it('removes the row on 404 with a toast', function () {
    deleteHistoryListPayload([
        deletableRow(3001, 'Live Product'),
        deletableRow(3002, 'Gone Product'),
    ]);

    Http::fake([
        'api.worthly.test/api/analyses/3002' => Http::response(['message' => 'Not found'], 404),
    ]);

    $component = Livewire::test(HistoryPage::class)
        ->assertSeeText('Live Product')
        ->assertSeeText('Gone Product');

    $component->call('requestDelete', 3002)
        ->call('confirmDelete')
        ->assertNoRedirect()
        ->assertSet('toast', 'Analysis no longer available.')
        ->assertSet('confirmDeleteId', null)
        ->assertSeeText('Live Product')
        ->assertDontSeeText('Gone Product')
        ->assertSeeText('Analysis no longer available.');

    expect($component->get('rows'))->toHaveCount(1);
    expect($component->get('rows')[0]['id'])->toBe(3001);
});

it('triggers the global 401 handler on 401', function () {
    deleteHistoryListPayload([
        deletableRow(4001, 'Auth Product'),
    ]);

    Cache::put('auth.user', ['name' => 'Ada']);
    Cache::put('analyses.recent', [['id' => 4001]]);

    Http::fake([
        'api.worthly.test/api/analyses/4001' => Http::response(['message' => 'Unauthenticated.'], 401),
    ]);

    Livewire::test(HistoryPage::class)
        ->call('requestDelete', 4001)
        ->call('confirmDelete')
        ->assertRedirect(route('login'));

    expect(app(SecureTokenStorage::class)->get())->toBeNull();
    expect(Cache::get('auth.user'))->toBeNull();
    expect(Cache::get('analyses.recent'))->toBeNull();
    expect(session('toast'))->toBe('Session expired. Please sign in again.');
});
