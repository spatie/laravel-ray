<?php

namespace Spatie\LaravelRay\Tests\Unit;

use Illuminate\Support\Arr;
use Spatie\LaravelRay\Tests\Concerns\MatchesOsSafeSnapshots;
use Spatie\LaravelRay\Tests\TestCase;
use Spatie\LaravelRay\Tests\TestClasses\TestJob;

class JobTest extends TestCase
{
    use MatchesOsSafeSnapshots;

    /** @test */
    public function it_can_automatically_send_jobs_to_ray()
    {
        ray()->showJobs();

        dispatch(new TestJob());

        ray()->stopShowingJobs();

        dispatch(new TestJob());

        $this->assertEquals('job_event', Arr::get($this->client->sentPayloads(), '0.payloads.0.type'));
        $this->assertCount(2, $this->client->sentPayloads());
    }
}
