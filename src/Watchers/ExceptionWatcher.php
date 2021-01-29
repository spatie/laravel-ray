<?php

namespace Spatie\LaravelRay\Watchers;

use Exception;
use Facade\FlareClient\Flare;
use Illuminate\Log\Events\MessageLogged;
use Illuminate\Support\Facades\Event;
use Spatie\LaravelRay\Ray;

class ExceptionWatcher extends Watcher
{
    public function register(): void
    {
        $this->enable();

        Event::listen(MessageLogged::class, function (MessageLogged $message) {
            if (! $this->enabled()) {
                return;
            }

            if (! $this->concernsException($message)) {
                return;
            }

            $exception = $message->context['exception'];

            /** @var Ray $ray */
            $ray = app(Ray::class);


            /** @var Flare $flare */
            $flare = app(Flare::class);

            /** @var \Facade\FlareClient\Report $report */
            $report = $flare->createReport($exception);

            $ray->exception($exception, ['flare_report' => $report->toArray()]);
        });
    }

    public function concernsException(MessageLogged $messageLogged): bool
    {
        if (! isset($messageLogged->context['exception'])) {
            return false;
        }

        if (! $messageLogged->context['exception'] instanceof Exception) {
            return false;
        }

        return true;
    }
}
