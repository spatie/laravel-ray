<?php

namespace Spatie\LaravelRay\Watchers;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Spatie\LaravelRay\Payloads\ExecutedQueryPayload;
use Spatie\LaravelRay\Ray;

class QueryWatcher extends Watcher
{
    public function register(): void
    {
        DB::listen(function (QueryExecuted $query) {
            if (! $this->enabled()) {
                return;
            }

            $payload = new ExecutedQueryPayload($query);

            app(Ray::class)->sendRequest($payload);
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
