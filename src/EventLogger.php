<?php

namespace Spatie\LaravelRay;

use Spatie\LaravelRay\Payloads\EventPayload;

class EventLogger
{
    protected bool $enabled = false;

    public function enable(): self
    {
        $this->enabled = true;

        return $this;
    }

    public function disable(): self
    {
        $this->enabled = false;

        return $this;
    }

    public function handleEvent(string $eventName, array $arguments): void
    {
        if (! $this->shouldHandleEvent($eventName)) {
            return;
        }

        $payload = new EventPayload($eventName, $arguments);

        app(Ray::class)->sendRequest($payload);
    }

    public function isLoggingEvents(): bool
    {
        return $this->enabled;
    }

    protected function shouldHandleEvent($event): bool
    {
        return $this->enabled;
    }
}
