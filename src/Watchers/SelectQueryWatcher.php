<?php

namespace Spatie\LaravelRay\Watchers;

use Spatie\Ray\Settings\Settings;

class SelectQueryWatcher extends ConditionalQueryWatcher
{
    public function register(): void
    {
        $settings = app(Settings::class);

        $this->enabled = $settings->send_select_queries_to_ray ?? false;

        $this->setConditionalCallback(function (string $query) {
            return str_starts_with(strtolower($query), 'select');
        });
    }
}
