<?php

namespace Spatie\LaravelRay\Tests\Unit;

use Spatie\LaravelRay\Tests\TestCase;

class MarkdownTest extends TestCase
{
    /** @test */
    public function it_can_render_and_send_markdown()
    {
        ray()->markdown('## Hello World!');

        $this->assertMatchesOsSafeSnapshot($this->client->sentRequests());
    }
}
