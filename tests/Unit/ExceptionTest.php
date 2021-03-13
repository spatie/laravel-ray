<?php

namespace Spatie\LaravelRay\Tests\Unit;

use Illuminate\Log\Events\MessageLogged;
use Spatie\LaravelRay\Tests\TestCase;

class ExceptionTest extends TestCase
{
    /** @test */
    public function it_will_not_send_exceptions_to_ray_if_disabled()
    {
        ray()->stopShowingExceptions();

        $hasError = false;

        try {
            event(new MessageLogged('warning', 'test', ['exception' => new \Exception('test')]));
        } catch(\Exception $e) {
            $hasError = true;
        }

        $this->assertFalse($hasError);
    }

}
