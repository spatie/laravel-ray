<?php

use Illuminate\Support\Facades\Log;
use Spatie\Ray\Settings\Settings;

it('will send logs to ray by default', function () {
    Log::info('hey');

    expect($this->client->sentRequests())->toHaveCount(1);
});

it('will not send logs to ray when disabled', function () {
    app(Settings::class)->send_log_calls_to_ray = false;

    Log::info('hey');

    expect($this->client->sentRequests())->toHaveCount(0);
});
