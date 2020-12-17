<?php

namespace Spatie\LaravelRay\Tests\Payloads;

use Spatie\LaravelRay\Payloads\MailablePayload;
use Spatie\LaravelRay\Tests\TestCase;
use Spatie\LaravelRay\Tests\TestClasses\TestMailable;
use Spatie\Snapshots\MatchesSnapshots;

class MailablePayloadTest extends TestCase
{
    use MatchesSnapshots;

    /** @test */
    public function it_can_render_a_mailable()
    {
        $mailable = new TestMailable();

        $payload = new MailablePayload($mailable);

        $this->assertMatchesSnapshot($payload->getContent()['html']);
    }
}
