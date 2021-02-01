<?php

namespace Spatie\LaravelRay\Tests\Unit;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Spatie\LaravelRay\Tests\Concerns\MatchesOsSafeSnapshots;
use Spatie\LaravelRay\Tests\TestCase;
use Spatie\LaravelRay\Tests\TestClasses\TestEvent;

class ViewTest extends TestCase
{
    /** @test */
    public function it_can_send_the_view_payload()
    {
        ray()->showViews();

        view('test')->render();

        $payloads = $this->client->sentPayloads();
        $this->assertCount(1, $payloads);
        $this->assertEquals('view', $payloads[0]['payloads'][0]['type']);
    }
}
