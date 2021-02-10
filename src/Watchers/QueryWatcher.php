<?php

namespace Spatie\LaravelRay\Watchers;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Spatie\LaravelRay\Payloads\ExecutedQueryPayload;
use Spatie\LaravelRay\Ray;
use Spatie\Ray\Settings\Settings;

class QueryWatcher extends Watcher
{
    public function register(): void
    {
        $settings = app(Settings::class);

        $this->enabled = $settings->send_queries_to_ray;

        DB::listen(function (QueryExecuted $query) {
            if (! $this->enabled()) {
                return;
            }

            $payload = new ExecutedQueryPayload($query);

            $ray = app(Ray::class)->sendRequest($payload);

            optional($this->rayProxy)->applyCalledMethods($ray);
        });
    }

    public function enable(): Watcher
    {
        DB::enableQueryLog();

        parent::enable();

        return $this;
    }

    public function disable(): Watcher
    {
        DB::disableQueryLog();

        parent::disable();

        return $this;
    }
}
