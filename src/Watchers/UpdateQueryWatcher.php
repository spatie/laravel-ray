<?php

namespace Spatie\LaravelRay\Watchers;

use Spatie\Ray\Settings\Settings;

class UpdateQueryWatcher extends ConditionalQueryWatcher
{
    public function __construct()
    {
        parent::__construct(function (string $query) {
            return str_starts_with(strtolower($query), 'update');
        });
    }

    public function register(): void
    {
        $settings = app(Settings::class);

        $this->enabled = $settings->send_update_queries_to_ray ?? false;
    }
}
