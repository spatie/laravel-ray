<?php

namespace Spatie\LaravelRay\Payloads;

use Spatie\Ray\ArgumentConverter;
use Spatie\Ray\Payloads\Payload;
use Throwable;

class JobEventPayload extends Payload
{
    protected object $event;

    protected object $job;

    protected ?Throwable $exception = null;

    public function __construct(object $event)
    {
        $this->event = $event;

        $this->job = unserialize($event->job->payload()['data']['command']);

        if (property_exists($event, 'exception')) {
            $this->exception = $event->exception ?? null;
        }
    }

    public function getType(): string
    {
        return 'job_event';
    }

    public function getContent(): array
    {
        return [
            'event_name' => class_basename($this->event),
            'job' => $this->job ? ArgumentConverter::convertToPrimitive($this->job) : null,
            'exception' => $this->exception ? ArgumentConverter::convertToPrimitive($this->exception) : null,
        ];
    }
}
