<?php

namespace Spatie\LaravelRay\Tests\Unit;

use Illuminate\Support\Facades\DB;
use Spatie\LaravelRay\Tests\TestCase;

class SlowQueryTest extends TestCase
{
    /** @test */
    public function it_can_start_logging_slow_queries()
    {
        ray()->showSlowQueries(0);

        DB::table('users')->get('id');

        $this->assertCount(1, $this->client->sentRequests());
    }

    /** @test */
    public function it_can_start_logging_slow_queries_using_alias()
    {
        ray()->slowQueries(0);

        DB::table('users')->get('id');

        $this->assertCount(1, $this->client->sentRequests());
    }

    /** @test */
    public function it_can_stop_logging_slow_queries()
    {
        ray()->showSlowQueries(0);

        DB::table('users')->get('id');
        DB::table('users')->get('id');
        $this->assertCount(2, $this->client->sentRequests());

        ray()->stopShowingSlowQueries();
        DB::table('users')->get('id');
        $this->assertCount(2, $this->client->sentRequests());
    }

    /** @test */
    public function calling_log_slow_queries_twice_will_not_log_all_queries_twice()
    {
        ray()->showSlowQueries(0);
        ray()->showSlowQueries(0);

        DB::table('users')->get('id');

        $this->assertCount(1, $this->client->sentRequests());
    }

    /** @test */
    public function it_can_log_all_slow_queries_in_a_callable()
    {
        ray()->showSlowQueries(0, function () {
            // will be logged
            DB::table('users')->where('id', 1)->get();
        });

        $this->assertCount(1, $this->client->sentRequests());

        // will not be logged
        DB::table('users')->get('id');
        $this->assertCount(1, $this->client->sentRequests());
    }

    /** @test */
    public function show_slow_queries_can_be_colorized()
    {
        $this->useRealUuid();

        ray()->showSlowQueries(0)->green();

        DB::table('users')->where('id', 1)->get();

        $sentPayloads = $this->client->sentRequests();

        $this->assertCount(2, $sentPayloads);
        $this->assertEquals($sentPayloads[0]['uuid'], $sentPayloads[1]['uuid']);
        $this->assertNotEquals('fakeUuid', $sentPayloads[0]['uuid']);
    }
}
