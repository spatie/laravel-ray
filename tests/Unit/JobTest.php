<?php

namespace Spatie\LaravelRay\Tests\Unit;

use Illuminate\Support\Arr;
use Spatie\LaravelRay\Tests\TestCase;
use Spatie\LaravelRay\Tests\TestClasses\TestJob;

class JobTest extends TestCase
{
    /** @test */
    public function it_can_automatically_send_jobs_to_ray()
    {
        ray()->showJobs();

        dispatch(new TestJob());

        ray()->stopShowingJobs();

        dispatch(new TestJob());

        $this->assertEquals('job_event', Arr::get($this->client->sentRequests(), '0.payloads.0.type'));
        $this->assertCount(2, $this->client->sentRequests());
    }

    /** @test */
    public function show_jobs_can_be_colorized()
    {
        $this->useRealUuid();

        ray()->showJobs()->green();

        dispatch(new TestJob());

        $sentPayloads = $this->client->sentRequests();

        $this->assertCount(4, $sentPayloads);
        $this->assertEquals($sentPayloads[0]['uuid'], $sentPayloads[1]['uuid']);
        $this->assertNotEquals('fakeUuid', $sentPayloads[0]['uuid']);
    }
}
