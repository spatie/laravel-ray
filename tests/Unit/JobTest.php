<?php

use Illuminate\Support\Arr;
use Spatie\LaravelRay\Tests\TestClasses\TestJob;

it('can automatically send jobs to ray', function () {
    ray()->showJobs();

    dispatch(new TestJob);

    ray()->stopShowingJobs();

    dispatch(new TestJob);

    expect(Arr::get($this->client->sentRequests(), '0.payloads.0.type'))->toEqual('job_event');
    expect($this->client->sentRequests())->toHaveCount(2);
});

it('show jobs can be colorized', function () {
    $this->useRealUuid();

    ray()->showJobs()->green();

    dispatch(new TestJob);

    $sentPayloads = $this->client->sentRequests();

    expect($sentPayloads)->toHaveCount(4);
    expect($sentPayloads[1]['uuid'])->toEqual($sentPayloads[0]['uuid']);
    expect($sentPayloads[0]['uuid'])->not->toEqual('fakeUuid');
});
