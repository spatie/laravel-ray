<?php

use Spatie\LaravelRay\Ray;

if (! function_exists('ray')) {
    function ray(...$args): Ray
    {
        return app(Ray::class)->send(...$args);
    }
}
