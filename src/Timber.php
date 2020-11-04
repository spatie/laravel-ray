<?php

namespace Spatie\LaravelTimber;

use Spatie\Timber\Timber as BaseTimber;

class Timber extends BaseTimber
{
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
}
