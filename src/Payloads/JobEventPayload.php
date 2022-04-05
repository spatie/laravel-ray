<?php

namespace Spatie\LaravelRay\Payloads;

use Illuminate\Queue\Jobs\SyncJob;
use Spatie\Ray\ArgumentConverter;
use Spatie\Ray\Payloads\Payload;

class JobEventPayload extends Payload
{
    /** @var object */
    protected $event;

    /** @var object|mixed */
    protected $job;

    /** @var \Throwable|null */
    protected $exception = null;

    public function __construct(object $event)
    {
        $this->event = $event;

        // The sync driver creates an intermediate job, the orignal job is inside the stored payload
        // For other drivers, the job is not altered, it can directly be used
        if ($event->job instanceof SyncJob) {
            $this->job = unserialize($event->job->payload()['data']['command']);
        } else {
            $this->job = $event->job;
        }

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
