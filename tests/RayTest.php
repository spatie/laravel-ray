<?php

namespace Spatie\LaravelRay\Tests;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Log;
use Spatie\LaravelRay\Tests\TestClasses\TestEvent;
use Spatie\LaravelRay\Tests\TestClasses\TestMailable;
use Spatie\LaravelRay\Tests\TestClasses\User;
use Spatie\Ray\Settings\Settings;
use Spatie\Snapshots\MatchesSnapshots;

class RayTest extends TestCase
{
    use MatchesSnapshots;

    /** @test */
    public function when_disabled_nothing_will_be_sent_to_ray()
    {
        app(Settings::class)->enable = false;

        ray('test');

        // re-enable for next tests
        ray()->enable();

        $this->assertCount(0, $this->client->sentPayloads());
    }

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
    public function it_will_not_blow_up_when_not_passing_anything()
    {
        ray();

        $this->assertCount(0, $this->client->sentPayloads());
    }

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
    public function it_can_check_enabled_status()
    {
        ray()->disable();
        $this->assertEquals(false, ray()->enabled());

        ray()->enable();
        $this->assertEquals(true, ray()->enabled());
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

        $this->assertCount(1, $this->client->sentPayloads());
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

    /** @test */
    public function it_can_send_a_class_based_event_to_ray()
    {
        ray()->showEvents();

        event(new TestEvent());

        ray()->stopShowingEvents();

        event('not showing this event');

        $this->assertCount(1, $this->client->sentPayloads());
        $this->assertEquals(TestEvent::class, Arr::get($this->client->sentPayloads(), '0.payloads.0.content.name'));
        $this->assertTrue(Arr::get($this->client->sentPayloads(), '0.payloads.0.content.class_based_event'));
    }

    /** @test */
    public function it_can_send_a_string_based_event_to_ray()
    {
        ray()->showEvents();

        $eventName = 'this is my event';

        event($eventName);

        ray()->stopShowingEvents();

        event('not showing this event');

        $this->assertCount(1, $this->client->sentPayloads());
        $this->assertEquals($eventName, Arr::get($this->client->sentPayloads(), '0.payloads.0.content.name'));
        $this->assertFalse(Arr::get($this->client->sentPayloads(), '0.payloads.0.content.class_based_event'));
    }

    /** @test */
    public function it_will_not_send_any_events_if_it_is_not_enabled()
    {
        event('test event');

        $this->assertCount(0, $this->client->sentPayloads());
    }

    /** @test */
    public function the_show_events_function_accepts_a_callable()
    {
        event('start event');

        ray()->showEvents(function () {
            event('event in callable');
        });

        event('end event');

        $this->assertCount(1, $this->client->sentPayloads());
        $this->assertEquals('event in callable', Arr::get($this->client->sentPayloads(), '0.payloads.0.content.name'));
    }

    /** @test */
    public function it_can_replace_the_remote_path_with_the_local_one()
    {
        app(Settings::class)->remote_path = 'tests';
        app(Settings::class)->local_path = 'local_tests';

        ray('test');

        $this->assertStringContainsString(
            'local_tests',
            Arr::get($this->client->sentPayloads(), '0.payloads.0.origin.file')
        );
    }
}
