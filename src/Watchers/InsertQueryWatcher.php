<?php

namespace Spatie\LaravelRay\Watchers;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Str;
use Spatie\Ray\Settings\Settings;

class InsertQueryWatcher extends ConditionalQueryWatcher
{
    public function register(): void
    {
        $settings = app(Settings::class);

        $this->enabled = $settings->send_insert_queries_to_ray ?? false;

        $this->setConditionalCallback(function (QueryExecuted $query) {
            return Str::startsWith(strtolower($query->sql), 'insert');
        });
    }
}
