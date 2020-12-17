<?php

namespace Spatie\LaravelRay\Payloads;

use Exception;
use Illuminate\Mail\Mailable;
use Spatie\Ray\Payloads\Payload;
use Spatie\Ray\ArgumentConvertor;

class MailablePayload extends Payload
{
    protected Mailable $mailable;

    public function __construct(Mailable $mailable)
    {
        $this->mailable = $mailable;
    }

    public function getType(): string
    {
        return 'mailable';
    }

    public function getContent(): array
    {
        return [
            'mailable_class' => get_class($this->mailable),
            'html' => $this->renderMailable(),
            'dumped_class' => ArgumentConvertor::convertToPrimitive($this->mailable),
        ];
    }

    protected function renderMailable(): string
    {
        try {
            return $this->mailable->render();
        } catch (Exception $exception) {
            return "Mailable could not be rendered because {$exception->getMessage()}";
        }
    }
}
