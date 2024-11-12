<?php

use Illuminate\Support\Arr;
use Spatie\LaravelRay\Tests\TestClasses\TestEvent;

it('can send a class based event to ray', function () {
    ray()->showEvents();

    event(new TestEvent());

    ray()->stopShowingEvents();

    event('not showing this event');

    expect($this->client->sentRequests())->toHaveCount(1);
    expect(Arr::get($this->client->sentRequests(), '0.payloads.0.content.name'))->toEqual(TestEvent::class);
    expect(Arr::get($this->client->sentRequests(), '0.payloads.0.content.class_based_event'))->toBeTrue();
});

it('can send a string based event to ray', function () {
    ray()->showEvents();

    $eventName = 'this is my event';

    event($eventName);

    ray()->stopShowingEvents();

    event('not showing this event');

    expect($this->client->sentRequests())->toHaveCount(1);
    expect(Arr::get($this->client->sentRequests(), '0.payloads.0.content.name'))->toEqual($eventName);
    expect(Arr::get($this->client->sentRequests(), '0.payloads.0.content.class_based_event'))->toBeFalse();
});

it('will not send any events if it is not enabled', function () {
    event('test event');

    expect($this->client->sentRequests())->toHaveCount(0);
});

it('the show events function accepts a callable', function () {
    event('start event');

    ray()->showEvents(function () {
        event('event in callable');
    });

    event('end event');

    expect($this->client->sentRequests())->toHaveCount(1);
    expect(Arr::get($this->client->sentRequests(), '0.payloads.0.content.name'))->toEqual('event in callable');
});

it('show events can be colorized', function () {
    $this->useRealUuid();

    ray()->showEvents()->green();

    event('my event');

    $sentPayloads = $this->client->sentRequests();

    expect($sentPayloads)->toHaveCount(2);
    expect($sentPayloads[1]['uuid'])->toEqual($sentPayloads[0]['uuid']);
    expect($sentPayloads[0]['uuid'])->not->toEqual('fakeUuid');
});
