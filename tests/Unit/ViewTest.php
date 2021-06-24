<?php

namespace Spatie\LaravelRay\Tests\Unit;

use Spatie\LaravelRay\Tests\TestCase;

class ViewTest extends TestCase
{
    /** @test */
    public function it_can_send_the_view_payload()
    {
        ray()->showViews();

        view('test')->render();

        $payloads = $this->client->sentRequests();
        $this->assertCount(1, $payloads);
        $this->assertEquals('view', $payloads[0]['payloads'][0]['type']);
    }

    /** @test */
    public function show_views_can_be_colorized()
    {
        $this->useRealUuid();

        ray()->showViews()->green();

        view('test')->render();

        $sentPayloads = $this->client->sentRequests();

        $this->assertCount(2, $sentPayloads);
        $this->assertEquals($sentPayloads[0]['uuid'], $sentPayloads[1]['uuid']);
        $this->assertNotEquals('fakeUuid', $sentPayloads[0]['uuid']);
    }
}
