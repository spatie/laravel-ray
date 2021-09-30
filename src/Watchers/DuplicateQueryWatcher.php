<?php

namespace Spatie\LaravelRay\Watchers;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\LaravelRay\Payloads\ExecutedQueryPayload;
use Spatie\LaravelRay\Ray;
use Spatie\Ray\Settings\Settings;

class DuplicateQueryWatcher extends Watcher
{
    /** @var string[] */
    protected $executedQueries = [];

    public function register(): void
    {
        $settings = app(Settings::class);

        $this->enabled = $settings->send_duplicate_queries_to_ray;

        if (! $this->enabled()) {
            return;
        }

        DB::listen(function (QueryExecuted $query) {

            $sql = Str::replaceArray('?', $query->bindings, $query->sql);

            $duplicated = in_array($sql, $this->executedQueries);

            $this->executedQueries[] = $sql;

            if (! $duplicated) {
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

    public function getExecutedQueries(): array
    {
        return $this->executedQueries;
    }

    public function disable(): Watcher
    {
        DB::disableQueryLog();

        parent::disable();

        return $this;
    }
}
