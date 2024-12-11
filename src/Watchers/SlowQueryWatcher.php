<?php

namespace Spatie\LaravelRay\Watchers;

use Illuminate\Database\Events\QueryExecuted;
use Spatie\Ray\Settings\Settings;

class SlowQueryWatcher extends ConditionalQueryWatcher
{
    protected $minimumTimeInMs = 500;

    public function register(): void
    {
        $settings = app(Settings::class);

        $this->enabled = $settings->send_slow_queries_to_ray ?? false;
        $this->minimumTimeInMs = $settings->slow_query_threshold_in_ms ?? $this->minimumTimeInMs;

        $this->setConditionalCallback(function (QueryExecuted $query) {
            return $query->time >= $this->minimumTimeInMs;
        });
    }

    public function setMinimumTimeInMilliseconds(float $milliseconds): self
    {
        $this->minimumTimeInMs = $milliseconds;

        return $this;
    }
}
