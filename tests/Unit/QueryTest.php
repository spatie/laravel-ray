<?php

namespace Spatie\LaravelRay\Tests\Unit;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Spatie\LaravelRay\Tests\TestCase;

class QueryTest extends TestCase
{
    /** @test */
    public function it_can_start_logging_queries()
    {
        ray()->showQueries();

        DB::table('users')->get('id');

        $this->assertCount(1, $this->client->sentPayloads());
    }

    /** @test */
    public function it_can_start_logging_queries_using_alias()
    {
        ray()->queries();

        DB::table('users')->get('id');

        $this->assertCount(1, $this->client->sentPayloads());
    }

    /** @test */
    public function it_can_stop_logging_queries()
    {
        ray()->showQueries();

        DB::table('users')->get('id');
        DB::table('users')->get('id');
        $this->assertCount(2, $this->client->sentPayloads());

        ray()->stopShowingQueries();
        DB::table('users')->get('id');
        $this->assertCount(2, $this->client->sentPayloads());
    }

    /** @test */
    public function calling_log_queries_twice_will_not_log_all_queries_twice()
    {
        ray()->showQueries();
        ray()->showQueries();

        DB::table('users')->get('id');

        $this->assertCount(1, $this->client->sentPayloads());
    }

    /** @test */
    public function it_can_log_all_queries_in_a_callable()
    {
        ray()->showQueries(function () {
            // will be logged
            DB::table('users')->where('id', 1)->get();
        });
        $this->assertCount(1, $this->client->sentPayloads());

        // will not be logged
        DB::table('users')->get('id');
        $this->assertCount(1, $this->client->sentPayloads());
    }

    /** @test */
    public function show_queries_can_be_colorized()
    {
        $this->useRealUuid();

        ray()->showQueries()->green();

        DB::table('users')->where('id', 1)->get();

        $sentPayloads = $this->client->sentPayloads();

        $this->assertCount(2, $sentPayloads);
        $this->assertEquals($sentPayloads[0]['uuid'], $sentPayloads[1]['uuid']);
        $this->assertNotEquals('fakeUuid', $sentPayloads[0]['uuid']);
    }

    /** @test */
    public function it_can_count_the_amount_of_executed_queries()
    {
        ray()->countQueries(function() {
            DB::table('users')->get('id');
            DB::table('users')->get('id');
            DB::table('users')->get('id');
        });

        $this->assertCount(1, $this->client->sentPayloads());

        $payload = $this->client->sentPayloads()[0];

        $this->assertEquals(3, Arr::get($payload, 'payloads.0.content.values.Count'));
    }
}
