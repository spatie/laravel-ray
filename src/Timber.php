<?php

namespace Spatie\LaravelTimber;

use Spatie\Timber\Timber as BaseTimber;

class Timber extends BaseTimber
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

    public function logQueries($callable = null): self
    {
        $wasLoggingQueries = $this->queryLogger()->isLoggingQueries();

        app(QueryLogger::class)->startLoggingQueries();

        if (! is_null($callable)) {
            $callable();

            if (! $wasLoggingQueries) {
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

    public function sendRequest(array $payloads): BaseTimber
    {
        if (! static::$enabled) {
            return $this;
        }

        return BaseTimber::sendRequest($payloads);
    }
}
