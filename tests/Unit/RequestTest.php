<?php

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Route;

it('can listen to requests', function () {
    Route::get('test', function () {
        return 'ok';
    });

    ray()->requests();

    $this->get('test');

    expect(Arr::get($this->client->sentRequests(), '0.payloads.0.content.values')['Response code'])->toEqual(200);
});

it('can listen to requests that return json', function () {
    Route::get('test-json', function () {
        return response()->json(['message' => 'ok']);
    });

    ray()->requests();

    $this->get('test-json');

    expect(Arr::get($this->client->sentRequests(), '0.payloads.0.content.values')['Response code'])->toEqual(200);
});

it('can listen to requests that return text', function () {
    Route::get('test-text', function () {
        return response('ok', 200, ['content-type' => 'text/plain']);
    });

    ray()->requests();

    $this->get('test-text');

    expect(Arr::get($this->client->sentRequests(), '0.payloads.0.content.values')['Response code'])->toEqual(200);
});

it('can listen to requests that return redirects', function () {
    Route::get('test-redirect', function () {
        return response()->redirectTo('/');
    });

    ray()->requests();

    $this->get('test-redirect');

    expect(Arr::get($this->client->sentRequests(), '0.payloads.0.content.values')['Response code'])->toEqual(302);
});

it('show request can be colorized', function () {
    $this->useRealUuid();

    ray()->showRequests()->green();

    $this->get('test-redirect');

    $sentPayloads = $this->client->sentRequests();

    expect($sentPayloads)->toHaveCount(2);
    expect($sentPayloads[1]['uuid'])->toEqual($sentPayloads[0]['uuid']);
    expect($sentPayloads[0]['uuid'])->not->toEqual('fakeUuid');
});
