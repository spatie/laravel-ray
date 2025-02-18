<?php

namespace Spatie\LaravelRay\Watchers;

use Illuminate\Mail\Events\MessageSending;
use Illuminate\Support\Facades\Event;
use Spatie\LaravelRay\Payloads\MailablePayload;
use Spatie\LaravelRay\Ray;
use Spatie\Ray\Settings\Settings;

class MailWatcher extends Watcher
{
    public function register(): void
    {
        $settings = app(Settings::class);

        $this->enabled = $settings->send_mails_to_ray;

        Event::listen([
            MessageSending::class,
        ], function (MessageSending $event) {
            if (! $this->enabled()) {
                return;
            }

            $payload = new MailablePayload($event->message->getHtmlBody());

            $ray = app(Ray::class)->sendRequest($payload);

            optional($this->rayProxy)->applyCalledMethods($ray);
        });
    }
}
