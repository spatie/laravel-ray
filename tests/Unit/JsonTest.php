<?php

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Route;

it('can send a json test response to ray', function () {
    Route::get('test', function () {
        return response()->json(['a' => 1]);
    });

    $this
        ->get('test')
        ->ray()
        ->assertSuccessful();

    expect($this->client->sentRequests())->toHaveCount(1);

    expect(Arr::get($this->client->sentRequests(), '0.payloads.0.content.status_code'))->toEqual(200);
    expect(Arr::get($this->client->sentRequests(), '0.payloads.0.content.headers'))->toContain('application/json');

    expect(Arr::get($this->client->sentRequests(), '0.payloads.0.content.content'))->toEqual('{"a":1}');
    expect(Arr::get($this->client->sentRequests(), '0.payloads.0.content.content'))->toEqual('{"a":1}');
    expect(Arr::get($this->client->sentRequests(), '0.payloads.0.content.json'))->not->toBeEmpty();
});

it('can send a regular test response to ray', function () {
    Route::get('test', function () {
        return response('hello', 201);
    });

    $this
        ->get('test')
        ->ray();

    expect($this->client->sentRequests())->toHaveCount(1);
    expect(Arr::get($this->client->sentRequests(), '0.payloads.0.content.status_code'))->toEqual(201);

    expect(Arr::get($this->client->sentRequests(), '0.payloads.0.content.headers'))->toContain('text/html');

    expect(Arr::get($this->client->sentRequests(), '0.payloads.0.content.content'))->toEqual('hello');

    expect(Arr::get($this->client->sentRequests(), '0.payloads.0.content.json'))->toBeEmpty();
});
