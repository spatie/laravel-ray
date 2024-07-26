<?php

namespace Spatie\LaravelRay\Watchers;

use Spatie\Ray\Settings\Settings;

class DeleteQueryWatcher extends ConditionalQueryWatcher
{
    public function __construct()
    {
        parent::__construct(function (string $query) {
            return str_starts_with(strtolower($query), 'delete');
        });
    }

    public function register(): void
    {
        $settings = app(Settings::class);

        $this->enabled = $settings->send_delete_queries_to_ray ?? false;
    }
}
