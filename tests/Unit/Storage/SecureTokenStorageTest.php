<?php

use App\Contracts\SecureTokenStorage;
use App\Support\Storage\InMemorySecureTokenStorage;

beforeEach(function () {
    $this->storage = new InMemorySecureTokenStorage;
});

it('implements the SecureTokenStorage contract', function () {
    expect($this->storage)->toBeInstanceOf(SecureTokenStorage::class);
});

it('stores, reads, and forgets a token', function () {
    expect($this->storage->get())->toBeNull();

    $this->storage->put('1|plain-text-sanctum-token');

    expect($this->storage->get())->toBe('1|plain-text-sanctum-token');

    $this->storage->forget();

    expect($this->storage->get())->toBeNull();
});

it('never returns a token after forget()', function () {
    $this->storage->put('first-token');
    $this->storage->forget();

    expect($this->storage->get())->toBeNull();

    $this->storage->put('second-token');
    $this->storage->forget();

    expect($this->storage->get())->toBeNull();
});
