<?php

namespace Spatie\LaravelRay\Watchers;

use BadMethodCallException;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\Event;
use Spatie\LaravelRay\Payloads\ExecutedQueryPayload;
use Spatie\LaravelRay\Ray;

class ConditionalQueryWatcher extends QueryWatcher
{
    protected $conditionalCallback;

    public function setConditionalCallback($conditionalCallback)
    {
        $this->conditionalCallback = $conditionalCallback;

        $this->listen();
    }

    public function register(): void
    {
        throw new BadMethodCallException('ConditionalQueryWatcher cannot be registered. Only its child classes.');
    }

    public function listen(): void
    {
        Event::listen(QueryExecuted::class, function (QueryExecuted $query) {
            if (! $this->enabled()) {
                return;
            }

            if (! $this->conditionalCallback) {
                return;
            }

            $ray = app(Ray::class);

            if (($this->conditionalCallback)($query->toRawSql())) {
                $payload = new ExecutedQueryPayload($query);

                $ray->sendRequest($payload);
            }

            optional($this->rayProxy)->applyCalledMethods($ray);
        });
    }
}
