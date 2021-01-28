<?php

namespace Spatie\LaravelRay\Watchers;

use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Queue\Events\JobQueued;
use Illuminate\Support\Facades\Event;
use Spatie\LaravelRay\JobLogger;
use Spatie\LaravelRay\Payloads\JobEventPayload;
use Spatie\LaravelRay\Ray;

class JobWatcher extends Watcher
{
    public function register(): void
    {
        Event::listen([
            JobQueued::class,
            JobProcessing::class,
            JobProcessed::class,
            JobFailed::class,
        ], function (object $event) {
            if (!$this->enabled()) {
                return;
            }

            $payload = new JobEventPayload($event);

            app(Ray::class)->sendRequest($payload);
        });
    }
}
