<?php

namespace Spatie\LaravelRay\Tests\Unit;

use Log;
use Spatie\LaravelRay\Tests\TestCase;
use Spatie\Ray\Settings\Settings;

class LogTest extends TestCase
{
    /** @test */
    public function it_will_send_logs_to_ray_by_default()
    {
        Log::info('hey');

        $this->assertCount(1, $this->client->sentPayloads());
    }

    /** @test */
    public function it_will_not_send_logs_to_ray_when_disabled()
    {
        app(Settings::class)->send_log_calls_to_ray = false;

        Log::info('hey');

        $this->assertCount(0, $this->client->sentPayloads());
    }
}
