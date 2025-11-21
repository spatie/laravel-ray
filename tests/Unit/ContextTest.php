<?php

namespace Spatie\LaravelRay\Tests\Unit;

use Illuminate\Support\Facades\Context;

it('can send all context', function () {
    if (! contextSupported()) {
        return;
    }

    Context::add('key', 'value');

    ray()->context();

    expect($this->client->sentRequests())->toHaveCount(2);

    $requests = $this->client->sentRequests();

    $clipboardData = $requests[0]['payloads'][0]['content']['meta']['0']['clipboard_data'];

    expect($clipboardData)->toContain('key', 'value');
});

it('can send specific context keys variadic', function () {
    if (! contextSupported()) {
        return;
    }

    Context::add('key1', 'value1');
    Context::add('key2', 'value2');
    Context::add('key3', 'value3');

    ray()->context('key1', 'key3');

    expect($this->client->sentRequests())->toHaveCount(2);

    $requests = $this->client->sentRequests();

    $clipboardData = $requests[0]['payloads'][0]['content']['meta']['0']['clipboard_data'];

    expect($clipboardData)->toContain('key1', 'key3');
    expect($clipboardData)->not()->toContain('key2');
});

it('can send specific context keys using an array', function () {
    if (! contextSupported()) {
        return;
    }

    Context::add('key1', 'value1');
    Context::add('key2', 'value2');
    Context::add('key3', 'value3');

    ray()->context(['key1', 'key3']);

    expect($this->client->sentRequests())->toHaveCount(2);

    $requests = $this->client->sentRequests();

    $clipboardData = $requests[0]['payloads'][0]['content']['meta']['0']['clipboard_data'];

    expect($clipboardData)->toContain('key1', 'key3');
    expect($clipboardData)->not()->toContain('key2');
});

it('can send all hidden context', function () {
    if (! contextSupported()) {
        return;
    }

    Context::addHidden('hidden-key', 'hidden-value');
    Context::add('visible-key', 'visible-value');

    ray()->hiddenContext();

    expect($this->client->sentRequests())->toHaveCount(2);

    $requests = $this->client->sentRequests();

    $clipboardData = $requests[0]['payloads'][0]['content']['meta']['0']['clipboard_data'];

    expect($clipboardData)->toContain('hidden-key');
    expect($clipboardData)->not()->toContain('visible-key');
});
