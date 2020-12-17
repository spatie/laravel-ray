<?php

namespace Spatie\LaravelRay\Tests;

use Illuminate\Support\Facades\DB;
use Log;
use Spatie\LaravelRay\Tests\TestClasses\TestMailable;
use Spatie\LaravelRay\Tests\TestClasses\User;
use Spatie\Snapshots\MatchesSnapshots;

class RayTest extends TestCase
{
    use MatchesSnapshots;

    /** @test */
    public function it_can_send_something_to_ray()
    {
        ray('a');
        $this->assertMatchesSnapshot($this->client->sentPayloads());
    }

    /** @test */
    public function it_will_send_logs_to_ray()
    {
        Log::info('hey');

        $this->assertCount(1, $this->client->sentPayloads());

        $this->assertMatchesSnapshot($this->client->sentPayloads());
    }

    /** @test */
    public function it_will_not_send_logs_to_ray_when_disabled()
    {
        config()->set(['ray.send_log_calls_to_ray' => false]);

        Log::info('hey');

        $this->assertCount(0, $this->client->sentPayloads());
    }

    /** @test */
    public function it_will_not_blow_up_when_not_passing_anything()
    {
        ray();

        $this->assertCount(0, $this->client->sentPayloads());
    }

    /** @test */
    public function it_can_start_logging_queries()
    {
        ray()->logQueries();

        DB::table('users')->get('id');

        $this->assertCount(1, $this->client->sentPayloads());
    }

    /** @test */
    public function it_can_stop_logging_queries()
    {
        ray()->logQueries();

        DB::table('users')->get('id');
        DB::table('users')->get('id');
        $this->assertCount(2, $this->client->sentPayloads());

        ray()->stopLoggingQueries();
        DB::table('users')->get('id');
        $this->assertCount(2, $this->client->sentPayloads());
    }

    /** @test */
    public function calling_log_queries_twice_will_not_log_all_queries_twice()
    {
        ray()->logQueries();
        ray()->logQueries();

        DB::table('users')->get('id');

        $this->assertCount(1, $this->client->sentPayloads());
    }

    /** @test */
    public function it_can_log_all_queries_in_a_callable()
    {
        ray()->logQueries(function () {
            // will be logged
            DB::table('users')->where('id', 1)->get();
        });
        $this->assertCount(1, $this->client->sentPayloads());

        // will not be logged
        DB::table('users')->get('id');
        $this->assertCount(1, $this->client->sentPayloads());
    }

    /** @test */
    public function it_can_be_disabled()
    {
        ray()->disable();
        ray('test');
        $this->assertCount(0, $this->client->sentPayloads());

        ray()->enable();
        ray('not test');
        $this->assertCount(1, $this->client->sentPayloads());
    }

    /** @test */
    public function it_can_log_dumps()
    {
        dump('test');

        $this->assertCount(1, $this->client->sentPayloads());
    }

    /** @test */
    public function it_can_send_models_to_ray()
    {
        $user = User::make(['email' => 'john@example.com']);

        ray()->model($user);

        $this->assertMatchesSnapshot($this->client->sentPayloads());
    }

    /** @test */
    public function it_has_a_chainable_collection_macro_to_send_things_to_ray()
    {
        $array = ['a', 'b', 'c'];

        $newArray = collect($array)->ray()->toArray();

        $this->assertEquals($newArray, $array);

        $this->assertCount(1, $this->client->sentPayloads());
    }

    /** @test */
    public function it_can_send_the_mailable_payload()
    {
        ray()->mailable(new TestMailable());

        $this->assertCount(1, $this->client->sentPayloads());
    }
}
