<?php

namespace Spatie\LaravelRay\Watchers;

use Illuminate\Log\Events\MessageLogged;
use Illuminate\Support\Facades\Event;
use Spatie\LaravelRay\Ray;
use Spatie\Ray\Payloads\ApplicationLogPayload;

class ApplicationLogWatcher extends Watcher
{
    public function register(): void
    {
        /** @var \Spatie\LaravelRay\Ray $ray */
        $ray = app(Ray::class);

        $this->enabled = $ray->settings->send_log_calls_to_ray;

        Event::listen(MessageLogged::class, function (MessageLogged $message) {
            if (! $this->shouldLogMessage($message)) {
                return;
            }

            $payload = new ApplicationLogPayload($message->message);

            /** @var Ray $ray */
            $ray = app(Ray::class);

            $ray->sendRequest($payload);

            if ($message->level === 'error') {
                $ray->color('red');
            }

            if ($message->level === 'warning') {
                $ray->color('orange');
            }
        });
    }

    protected function shouldLogMessage(MessageLogged  $message): bool
    {
        if (! $this->enabled()) {
            return false;
        }

        /** @var Ray $ray */
        $ray = app(Ray::class);

        if (! $ray->settings->send_log_calls_to_ray) {
            return false;
        }

        /*
         * uncomment this when exception logging is stable in Ray
         *
        if ((new ExceptionWatcher())->concernsException($message)) {
            return false;
        }
        */


        if ((new LoggedMailWatcher())->concernsLoggedMail($message)) {
            return false;
        }

        return true;
    }
}
