<?php

namespace Spatie\LaravelRay;

use Composer\InstalledVersions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Str;
use Spatie\LaravelRay\Payloads\MailablePayload;
use Spatie\LaravelRay\Payloads\ModelPayload;
use Spatie\Ray\Ray as BaseRay;

class Ray extends BaseRay
{
    public static bool $enabled = true;

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

    public function enabled(): bool
    {
        return self::$enabled;
    }

    public function disabled(): bool
    {
        return ! self::$enabled;
    }

    public function loggedMail(string $loggedMail): self
    {
        $html = '<html' . Str::between($loggedMail, '<html', '</html>') . '</html>';

        $payload = new MailablePayload($html);

        $this->sendRequest($payload);

        return $this;
    }

    public function mailable(Mailable $mailable): self
    {
        $payload = MailablePayload::forMailable($mailable);

        $this->sendRequest($payload);

        return $this;
    }

    public function model(?Model $model): self
    {
        $payload = new ModelPayload($model);

        $this->sendRequest($payload);

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

    public function events($callable = null): self
    {
        return $this->showEvents($callable);
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

    public function queries($callable = null): self
    {
        return $this->showQueries($callable);
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

    /**
     * @param \Spatie\Ray\Payloads\Payload|\Spatie\Ray\Payloads\Payload[] $payloads
     * @param array $meta
     *
     * @return \Spatie\Ray\Ray
     * @throws \Exception
     */
    public function sendRequest($payloads, array $meta = []): BaseRay
    {
        if (! static::$enabled) {
            return $this;
        }

        $meta = [
            'laravel_version' => app()->version(),
        ];

        if (class_exists(InstalledVersions::class)) {
            $meta['laravel_ray_package_version'] = InstalledVersions::getVersion('spatie/laravel-ray');
        }

        return BaseRay::sendRequest($payloads, $meta);
    }
}
