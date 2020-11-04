<?php

use Spatie\LaravelTimber\Timber;

if (! function_exists('timber')) {
    function timber(...$args): Timber
    {
        return app(Timber::class)->send(...$args);
    }
}
