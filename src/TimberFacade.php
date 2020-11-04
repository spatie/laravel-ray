<?php

namespace Spatie\Timber;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Spatie\Timber\Timber
 */
class TimberFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'laravel-timber';
    }
}
