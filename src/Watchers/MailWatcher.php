<?php

namespace Spatie\LaravelRay\Watchers;

use Illuminate\Log\Events\MessageLogged;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Spatie\LaravelRay\Payloads\MailablePayload;
use Spatie\LaravelRay\Ray;
use Spatie\Ray\Settings\Settings;

class MailWatcher extends Watcher
{
    public function register(): void
    {
        $settings = app(Settings::class);

        if ($settings->send_mails_to_ray ?? true) {
            $this->enable();
        }

        $this->supportsMessageSendingEvent()
            ? $this->registerMessageSendingEventListener()
            : $this->listenForLoggedMails();
    }

    protected function registerMessageSendingEventListener(): void
    {
        Event::listen([
            MessageSending::class,
        ], function (MessageSending $event) {
            if (! $this->enabled()) {
                return;
            }

            $payload = new MailablePayload($event->message->getHtmlBody() ?? $event->message->getTextBody());

            $ray = app(Ray::class)->sendRequest($payload);

            optional($this->rayProxy)->applyCalledMethods($ray);
        });
    }

    public function listenForLoggedMails(): void
    {
        Event::listen(MessageLogged::class, function (MessageLogged $messageLogged) {
            if (! $this->enabled()) {
                return;
            }

            if (! $this->concernsLoggedMail($messageLogged)) {
                return;
            }

            /** @var Ray $ray */
            $ray = app(Ray::class);

            $ray->loggedMail($messageLogged->message);
        });
    }

    public function concernsLoggedMail(MessageLogged $messageLogged): bool
    {
        if (! Str::contains($messageLogged->message, 'Message-ID')) {
            return false;
        }

        if (! Str::contains($messageLogged->message, 'To:')) {
            return false;
        }

        return true;
    }

    public function supportsMessageSendingEvent(): bool
    {
        return version_compare(app()->version(), '11.0.0',  '>=');
    }
}
