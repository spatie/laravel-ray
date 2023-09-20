<?php

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Log;
use Spatie\LaravelRay\Ray;
use Spatie\LaravelRay\RayServiceProvider;
use Spatie\LaravelRay\Tests\TestClasses\TestMailable;
use Spatie\LaravelRay\Tests\TestClasses\User;
use Spatie\Ray\Settings\Settings;

it('when disabled nothing will be sent to ray', function () {
    app(Settings::class)->enable = false;

    ray('test');

    // re-enable for next tests
    ray()->enable();

    expect($this->client->sentRequests())->toHaveCount(0);
});

it('will send logs to ray by default', function () {
    Log::info('hey');

    expect($this->client->sentRequests())->toHaveCount(1);
});

it('can disable deprecated notices', function () {
    Log::warning('Deprecated');
    Log::warning('deprecated');

    expect($this->client->sentRequests())->toHaveCount(0);
});

it('can enable deprecated notices', function () {
    app(Settings::class)->send_deprecated_notices_to_ray = true;

    Log::warning('Deprecated');
    Log::warning('deprecated');

    expect($this->client->sentRequests())->toHaveCount(4);
});

it('will not send dumps to ray when disabled', function () {
    app(Settings::class)->send_dumps_to_ray = false;

    dump('');

    expect($this->client->sentRequests())->toHaveCount(0);
});

it('will send dumps to ray by default', function () {
    dump('spatie');

    expect($this->client->sentRequests())->toHaveCount(1);
});

it('will not send logs to ray when disabled', function () {
    app(Settings::class)->send_log_calls_to_ray = false;

    Log::info('hey');

    expect($this->client->sentRequests())->toHaveCount(0);
});

it('will not blow up when not passing anything', function () {
    ray();

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
    $settings = app(Settings::class);

    $settings->remote_path = __DIR__;
    $settings->local_path = 'local_tests';

    ray('test');

    expect(Arr::get($this->client->sentRequests(), '0.payloads.0.origin.file'))->toContain('local_tests');
});

it('will automatically use specialized payloads', function () {
    ray(new TestMailable(), new User());

    $payloads = $this->client->sentRequests();

    expect($payloads[0]['payloads'][0]['type'])->toEqual('mailable');
    expect($payloads[0]['payloads'][1]['type'])->toEqual('eloquent_model');
});

it('sends an environment payload', function () {
    ray()->env([], __DIR__ . '/stubs/dotenv.env');

    $payloads = $this->client->sentRequests();

    expect($payloads[0]['payloads'][0]['type'])->toEqual('table');
    expect($payloads[0]['payloads'][0]['content']['label'])->toEqual('.env');
    expect($payloads[0]['payloads'][0]['content']['values']['APP_ENV'])->toEqual('local');
    expect($payloads[0]['payloads'][0]['content']['values']['DB_DATABASE'])->toEqual('ray_test');
    expect($payloads[0]['payloads'][0]['content']['values']['SESSION_LIFETIME'])->toEqual('120');
    expect(is_countable($payloads[0]['payloads'][0]['content']['values']) ? count($payloads[0]['payloads'][0]['content']['values']) : 0)->toBeGreaterThanOrEqual(17);
});

it('sends a filtered environment payload', function () {
    ray()->env(['APP_ENV', 'DB_DATABASE'], __DIR__ . '/stubs/dotenv.env');

    $payloads = $this->client->sentRequests();

    expect($payloads[0]['payloads'][0]['type'])->toEqual('table');
    expect($payloads[0]['payloads'][0]['content']['label'])->toEqual('.env');
    expect($payloads[0]['payloads'][0]['content']['values']['APP_ENV'])->toEqual('local');
    expect($payloads[0]['payloads'][0]['content']['values']['DB_DATABASE'])->toEqual('ray_test');
    expect($payloads[0]['payloads'][0]['content']['values'])->toHaveCount(2);
});

it('the project name will automatically be set if it something other than laravel', function () {
    (new RayServiceProvider($this->app))->setProjectName();

    expect(Ray::$projectName)->toEqual('');

    config()->set('app.name', 'my-project');

    (new RayServiceProvider($this->app))->setProjectName();

    expect(Ray::$projectName)->toEqual('my-project');
});

it('still boots and works although the DB facade has not been bound', function () {
    unset($this->app['db']);
    Facade::clearResolvedInstance('db');

    (new RayServiceProvider($this->app))->boot();

    ray('foo');

    expect($this->client->sentRequests())->toHaveCount(1);
});
