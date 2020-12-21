<?php

namespace Spatie\LaravelRay\Payloads;

use Exception;
use Illuminate\Mail\Mailable;
use Spatie\Ray\Payloads\Payload;
use Spatie\Ray\ArgumentConvertor;

class MailablePayload extends Payload
{
    protected Mailable $mailable;

    public function __construct(Mailable $mailable)
    {
        $this->mailable = $mailable;
    }

    public function getType(): string
    {
        return 'mailable';
    }

    public function getContent(): array
    {
        return [
            'mailable_class' => get_class($this->mailable),
            'html' => $this->renderMailable(),
            'dumped_class' => ArgumentConvertor::convertToPrimitive($this->mailable),
        ];
    }

    protected function renderMailable(): string
    {
        try {
            return $this->mailable->render();
        } catch (Exception $exception) {
            return "Mailable could not be rendered because {$exception->getMessage()}";

        }


- [backtrace](https://github.com/spatie/backtrace)
- [crypto](https://github.com/spatie/crypto)
- [docker](https://github.com/spatie/docker)
- [laravel-backup-server](https://github.com/spatie/laravel-backup-server)
- [laravel-context-demo](https://github.com/spatie/laravel-context-demo)
- [laravel-cronless-schedule](https://github.com/spatie/laravel-cronless-schedule)
- [laravel-dashboard](https://github.com/spatie/laravel-dashboard)
- [laravel-dashboard-belgian](https://github.com/spatie/laravel-dashboard-belgian-trains-tile)
- [laravel-dashboard-calendar](https://github.com/spatie/laravel-dashboard-calendar-tile)
- [laravel-dashboard-oh-dear](https://github.com/spatie/laravel-dashboard-oh-dear-uptime-tile)
- [laravel-dashboard-skeleton](https://github.com/spatie/laravel-dashboard-skeleton-tile)
- [laravel-dashboard-time](https://github.com/spatie/laravel-dashboard-time-weather-tile)
- [laravel-dashboard-twitter](https://github.com/spatie/laravel-dashboard-twitter-tile)
- [laravel-dashboard-velo-tile](https://github.com/spatie/laravel-dashboard-velo-tile)
- [laravel-disk-monitor](https://github.com/spatie/laravel-disk-monitor)
- [laravel-log-dumper](https://github.com/spatie/laravel-log-dumper)
- [laravel-medialibrary-pro](https://github.com/spatie/laravel-medialibrary-pro-app)
- [laravel-morph-map-generator](https://github.com/spatie/laravel-morph-map-generator)
- [laravel-multitenancy](https://github.com/spatie/laravel-multitenancy)
- [laravel-queued-db-cleanup](https://github.com/spatie/laravel-queued-db-cleanup)
- [laravel-random-command](https://github.com/spatie/laravel-random-command)
- [laravel-route-attributes](https://github.com/spatie/laravel-route-attributes)
- [laravel-schedule-monitor](https://github.com/spatie/laravel-schedule-monitor)
- [laravel-settings](https://github.com/spatie/laravel-settings)
- [laravel-short-schedule](https://github.com/spatie/laravel-short-schedule)
- [laravel-statistics](https://github.com/spatie/laravel-statistics)
- [laravel-stubs](https://github.com/spatie/laravel-stubs)
- [laravel-typescript](https://github.com/spatie/laravel-typescript-transformer)
- [laravel-utm-forwarder](https://github.com/spatie/laravel-utm-forwarder)
- [pest-plugin-snapshots](https://github.com/spatie/pest-plugin-snapshots)
- [postcss-purgecss-laravel](https://github.com/spatie/postcss-purgecss-laravel)
- [spatie-price-api](https://github.com/spatie/spatie-price-api)
- [ssh](https://github.com/spatie/ssh)
- [statamic-algolia-places](https://github.com/spatie/statamic-algolia-places)
- [statamic-mailcoach](https://github.com/spatie/statamic-mailcoach)
- [sun](https://github.com/spatie/sun)
- [twitter-labs](https://github.com/spatie/twitter-labs)
- [typescript-transformer](https://github.com/spatie/typescript-transformer)
- [unit-conversions](https://github.com/spatie/unit-conversions)
    }


}
