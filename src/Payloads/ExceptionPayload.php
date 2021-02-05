<?php

namespace Spatie\LaravelRay\Payloads;

use Spatie\Ray\Payloads\Payload;

class ExceptionPayload extends Payload
{
    /** @var \Exception */
    protected $exception;

    public function __construct(\Exception $exception)
    {
        $this->exception = $exception;
    }

    public function getType(): string
    {
        return 'custom';
    }

    public function getContent(): array
    {
        return [
            'message' => $this->exception->getMessage(),
        ];
    }
}
