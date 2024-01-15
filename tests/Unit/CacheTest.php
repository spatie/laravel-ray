<?php

use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    config('cache.default', 'array');
});

it('can detect when something gets cached', function () {
    ray()->showCache();

    Cache::put('cached-key', 'cached-value');

    assertMatchesOsSafeSnapshot($this->client->sentRequests());
});

it('will not report caches by default', function () {
    Cache::put('cached-key', 'cached-value');

    expect($this->client->sentRequests())->toHaveCount(0);
});

it('the cache watcher can be disabled', function () {
    ray()->showCache();

    Cache::put('cached-key', 'cached-value');

    ray()->stopShowingCache();

    Cache::put('another-key', 'another-value');

    expect($this->client->sentRequests())->toHaveCount(1);
});

it('can detect when the cache is hit', function () {
    ray()->showCache();

    Cache::put('cached-key', 'cached-value');

    Cache::get('cached-key');

    assertMatchesOsSafeSnapshot($this->client->sentRequests());
});

it('can detect when the cache is missed', function () {
    ray()->showCache();

    Cache::get('cached-key');

    assertMatchesOsSafeSnapshot($this->client->sentRequests());
});

it('can detect when something gets temporarily cached', function () {
    ray()->showCache();

    Cache::put('cached-key', 'cached-value', 10);

    assertMatchesOsSafeSnapshot($this->client->sentRequests());
});

it('can detect when something is cleared from the cache', function () {
    ray()->showCache();

    Cache::put('cached-key', 'cached-value', 10);

    Cache::pull('cached-key');

    assertMatchesOsSafeSnapshot($this->client->sentRequests());
});
