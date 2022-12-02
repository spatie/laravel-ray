<?php

use Illuminate\Support\Arr;
use Spatie\LaravelRay\Tests\TestClasses\TestMailable;
use Spatie\LaravelRay\Tests\TestClasses\User;
use Spatie\Ray\Settings\Settings;

it('when disabled nothing will be sent to ray', function () {
    app(Settings::class)->enable = false;

    ray('test');

    ray()->enable();

    expect($this->client->sentRequests())->toHaveCount(0);
});

it('can be disabled', function () {
    ray()->disable();
    ray('test');
    expect($this->client->sentRequests())->toHaveCount(0);

    ray()->enable();
    ray('not test');
    expect($this->client->sentRequests())->toHaveCount(1);
});

it('will not blow up when not passing anything', function () {
    ray();

    expect($this->client->sentRequests())->toHaveCount(0);
});

it('can check enabled status', function () {
    ray()->disable();
    expect(ray()->enabled())->toEqual(false);

    ray()->enable();
    expect(ray()->enabled())->toEqual(true);
});

it('can check disabled status', function () {
    ray()->disable();
    expect(ray()->disabled())->toEqual(true);

    ray()->enable();
    expect(ray()->disabled())->toEqual(false);
});

it('can replace the remote path with the local one', function () {
    app(Settings::class)->remote_path = __DIR__;
    app(Settings::class)->local_path = 'local_tests';

    ray('test');

    expect(Arr::get($this->client->sentRequests(), '0.payloads.0.origin.file'))->toContain('local_tests');
});

it('will automatically use specialized payloads', function () {
    ray(new TestMailable(), new User());

    $payloads = $this->client->sentRequests();

    expect($payloads[0]['payloads'][0]['type'])->toEqual('mailable');
    expect($payloads[0]['payloads'][1]['type'])->toEqual('eloquent_model');
});
