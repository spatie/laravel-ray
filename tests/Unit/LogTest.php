<?php

namespace Spatie\LaravelRay\Tests\Unit;

use Illuminate\Support\Facades\Log;
use Spatie\LaravelRay\Tests\TestCase;
use Spatie\LaravelRay\Watchers\ExceptionWatcher;
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

    /** @test */
    public function it_will_send_logs_with_exceptions_to_ray_when_enabled()
    {
        app(Settings::class)->send_log_calls_to_ray = true;
        app(ExceptionWatcher::class)->enable();

        // counters increase by two each time because each Log::error() call
        // also sends a color('red') payload
        $contexts = [
            3 => ['exception' => new \Exception('test')],
            5 => ['exception' => new \stdClass()],
            7 => [],
        ];

        foreach ($contexts as $counter => $context) {
            Log::error('hey', $context);

            $this->assertCount($counter, $this->client->sentPayloads());
        }

        $this->assertMatchesOsSafeSnapshot($this->client->sentPayloads());
    }
}
