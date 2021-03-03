<?php

namespace Spatie\LaravelRay\Tests\Unit;

use Spatie\LaravelRay\Tests\TestCase;
use Spatie\Ray\Origin\Hostname;

class MarkdownTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Hostname::set('fake-hostname');
    }

    /** @test */
    public function it_can_render_and_send_markdown()
    {
        ray()->markdown('## Hello World!');

        $this->assertMatchesOsSafeSnapshot($this->client->sentPayloads());
    }
}
