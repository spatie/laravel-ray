<?php

namespace Spatie\LaravelRay;

use Spatie\LaravelRay\Payloads\JobEventPayload;

class JobLogger
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

    public function handleJobEvent(object $jobEvent): void
    {
        $payload = new JobEventPayload($jobEvent);

        app(Ray::class)->sendRequest($payload);
    }

    public function isLoggingJobs(): bool
    {
        return $this->enabled;
    }
}
