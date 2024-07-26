<?php

namespace Spatie\LaravelRay\Watchers;

use Illuminate\Database\Events\QueryExecuted;
use Spatie\Ray\Settings\Settings;

class UpdateQueryWatcher extends ConditionalQueryWatcher
{
    public function register(): void
    {
        $settings = app(Settings::class);

        $this->enabled = $settings->send_update_queries_to_ray ?? false;

        $this->setConditionalCallback(function (QueryExecuted $query) {
            return str_starts_with(strtolower($query->toRawSql()), 'update');
        });
    }
}
