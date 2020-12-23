<?php

namespace Spatie\LaravelRay\Payloads;

use Spatie\Ray\ArgumentConvertor;
use Spatie\Ray\Payloads\Payload;

class EventPayload extends Payload
{
    protected string $eventName;
    protected ?object $event = null;
    protected array $payload = [];

    public function __construct(string $eventName, array $payload)
    {
        $this->eventName = $eventName;

        class_exists($eventName)
            ? $this->event = $payload[0]
            : $this->payload = $payload;
    }

    public function getType(): string
    {
        return 'event';
    }

    public function getContent(): array
    {
        return [
            'name' => $this->eventName,
            'event' => ArgumentConvertor::convertToPrimitive($this->event),
            'payload' => ArgumentConvertor::convertToPrimitive($this->payload),
            'class_based_event' => ! is_null($this->event),
        ];
    }
}
