<?php

it('can send the view payload', function () {
    ray()->showViews();

    view('test')->render();

    $payloads = $this->client->sentRequests();
    expect($payloads)->toHaveCount(1);
    expect($payloads[0]['payloads'][0]['type'])->toEqual('view');
});

it('show views can be colorized', function () {
    $this->useRealUuid();

    ray()->showViews()->green();

    view('test')->render();

    $sentPayloads = $this->client->sentRequests();

    expect($sentPayloads)->toHaveCount(2);
    expect($sentPayloads[1]['uuid'])->toEqual($sentPayloads[0]['uuid']);
    expect($sentPayloads[0]['uuid'])->not->toEqual('fakeUuid');
});
