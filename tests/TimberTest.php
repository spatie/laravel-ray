<?php

namespace Spatie\LaravelTimber\Tests;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Log;
use Spatie\Snapshots\MatchesSnapshots;

class TimberTest extends TestCase
{
    use MatchesSnapshots;

    /** @test */
    public function it_can_send_a_something_to_timber()
    {
        timber('a');

        $this->assertMatchesSnapshot($this->client->sentPayloads());
    }

    /** @test */
    public function it_will_send_logs_to_timber()
    {
        Log::info('hey');

        $this->assertCount(1, $this->client->sentPayloads());

        $this->assertMatchesSnapshot($this->client->sentPayloads());
    }

    /** @test */
    public function it_will_not_send_logs_to_timber_when_disabled()
    {
        config()->set(['timber.send_log_calls_to_timber' => false]);

        Log::info('hey');

        $this->assertCount(0, $this->client->sentPayloads());
    }
}
