<?php

namespace Spatie\LaravelRay;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Str;
use Spatie\LaravelRay\Payloads\MailablePayload;
use Spatie\LaravelRay\Payloads\ModelPayload;
use Spatie\Ray\Payloads\Payload;
use Spatie\Ray\Ray as BaseRay;
use Symfony\Component\Console\Output\OutputInterface;

class Ray extends BaseRay
{
    public static bool $enabled = true;

    protected ?OutputInterface $consoleOutput = null;

    public function setConsoleOutput(?OutputInterface $consoleOutput): self
    {
        $this->consoleOutput = $consoleOutput;

        return $this;
    }

    public function enable(): self
    {
        self::$enabled = true;

        return $this;
    }

    public function disable(): self
    {
        self::$enabled = false;

        return $this;
    }

    public function loggedMail(string $loggedMail): self
    {
        $html = '<html' . Str::between($loggedMail, '<html', '</html>') . '</html>';

        $payload = new MailablePayload($html);

        $this->sendRequest([$payload]);

        return $this;
    }

    public function mailable(Mailable $mailable): self
    {
        $payload = MailablePayload::forMailable($mailable);

        $this->sendRequest([$payload]);

        return $this;
    }

    public function model(Model $model): self
    {
        $payload = new ModelPayload($model);

        $this->sendRequest([$payload]);

        return $this;
    }

    public function showEvents($callable = null): self
    {
        $wasLoggingEvents = $this->eventLogger()->isLoggingEvents();

        $this->eventLogger()->enable();

        if ($callable) {
            $callable();

            if (! $wasLoggingEvents) {
                $this->eventLogger()->disable();
            }
        }

        return $this;
    }

    public function stopShowingEvents(): self
    {
        /** @var \Spatie\LaravelRay\EventLogger $eventLogger */
        $eventLogger = app(EventLogger::class);

        $eventLogger->disable();

        return $this;
    }

    public function showQueries($callable = null): self
    {
        $wasLoggingQueries = $this->queryLogger()->isLoggingQueries();

        $this->queryLogger()->startLoggingQueries();

        if (! is_null($callable)) {
            $callable();

            if (! $wasLoggingQueries) {
                $this->stopShowingQueries();
            }
        }

        return $this;
    }

    public function stopShowingQueries(): self
    {
        $this->queryLogger()->stopLoggingQueries();

        return $this;
    }

    protected function eventLogger(): EventLogger
    {
        return app(EventLogger::class);
    }

    protected function queryLogger(): QueryLogger
    {
        return app(QueryLogger::class);
    }

    public function sendRequest(array $payloads): BaseRay
    {
        if (! static::$enabled) {
            return $this;
        }

        $ray = BaseRay::sendRequest($payloads);

        if ($this->consoleOutput) {
            collect($payloads)->each(function (Payload $payload) {
                $payload->outputToConsole($this->consoleOutput);
            });
        }

        return $ray;
    }
}
