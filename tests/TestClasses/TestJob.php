<?php

namespace Spatie\LaravelRay\Tests\TestClasses;

use Illuminate\Contracts\Queue\ShouldQueue;

class TestJob implements ShouldQueue
{
    public function handle()
    {
    }
}
