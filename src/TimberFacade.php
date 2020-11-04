<?php

namespace Spatie\LaravelTimber;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Spatie\LaravelTimber\Timber
 */
class TimberFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'laravel-timber';
    }
}
