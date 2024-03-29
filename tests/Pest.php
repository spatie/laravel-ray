<?php

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
*/

use Illuminate\Support\Facades\Context;
use Spatie\LaravelRay\Tests\TestCase;

uses(TestCase::class)->in('.');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
*/

function assertMatchesOsSafeSnapshot($data): void
{
    // fix paths when running unit tests on windows platform (github actions)
    $json = json_encode($data);
    $json = str_replace('D:\\\\a\\\\laravel-ray\\\\laravel-ray', '', $json);
    $json = str_replace('\\\\', '/', $json);


    test()->expect($json)->toMatchJsonSnapshot();
}

function contextSupported(): bool
{
    return class_exists(Context::class);
}
