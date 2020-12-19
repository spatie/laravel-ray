<?php

namespace Spatie\LaravelRay;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Mail\Mailable;
use Spatie\LaravelRay\Payloads\MailablePayload;
use Spatie\LaravelRay\Payloads\ModelPayload;
use Spatie\Ray\Client;
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

    public function mailable(Mailable $mailable): self
    {
        $payload = new MailablePayload($mailable);

        $this->sendRequest([$payload]);

        return $this;
    }

    public function model(Model $model): self
    {
        $payload = new ModelPayload($model);

        $this->sendRequest([$payload]);

        return $this;
    }

    public function logQueries($callable = null): self
    {
        $wasLoggingQueries = $this->queryLogger()->isLoggingQueries();

        app(QueryLogger::class)->startLoggingQueries();

        if (!is_null($callable)) {
            $callable();

            if (!$wasLoggingQueries) {
                $this->stopLoggingQueries();
            }
        }

        return $this;
    }

    public function stopLoggingQueries(): self
    {
        $this->queryLogger()->stopLoggingQueries();

        return $this;
    }

    protected function queryLogger(): QueryLogger
    {
        return app(QueryLogger::class);
    }

    public function sendRequest(array $payloads): BaseRay
    {
        if (!static::$enabled) {
            return $this;
        }

        $ray =  BaseRay::sendRequest($payloads);

        if ($this->consoleOutput) {
            collect($payloads)->each(function(Payload $payload) {
                $payload->outputToConsole($this->consoleOutput);
            });
        }

        return $ray;
    }
}
