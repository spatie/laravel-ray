<?php

namespace Spatie\LaravelTimber\Tests;

use Illuminate\Support\Facades\DB;
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

    /** @test */
    public function it_will_not_blow_up_when_not_passing_anything()
    {
        timber();

        $this->assertCount(0, $this->client->sentPayloads());
    }

    /** @test */
    public function it_can_start_logging_queries()
    {
        timber()->logQueries();

        DB::table('users')->get('id');

        $this->assertCount(1, $this->client->sentPayloads());
    }

    /** @test */
    public function it_can_stop_logging_queries()
    {
        timber()->logQueries();

        DB::table('users')->get('id');
        DB::table('users')->get('id');
        $this->assertCount(2, $this->client->sentPayloads());

        timber()->stopLoggingQueries();
        DB::table('users')->get('id');
        $this->assertCount(2, $this->client->sentPayloads());
    }

    /** @test */
    public function calling_log_queries_twice_will_not_log_all_queries_twice()
    {
        timber()->logQueries();
        timber()->logQueries();

        DB::table('users')->get('id');

        $this->assertCount(1, $this->client->sentPayloads());
    }

    /** @test */
    public function it_can_log_all_queries_in_a_callable()
    {
        timber()->logQueries(function () {
            // will be logged
            DB::table('users')->where('id', 1)->get();
        });
        $this->assertCount(1, $this->client->sentPayloads());

        // will not be logged
        DB::table('users')->get('id');
        $this->assertCount(1, $this->client->sentPayloads());
    }
}
