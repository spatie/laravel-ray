<?php

namespace Spatie\LaravelRay\Tests\Payloads;

use Spatie\LaravelRay\Payloads\MailablePayload;
use Spatie\LaravelRay\Tests\TestCase;
use Spatie\LaravelRay\Tests\TestClasses\TestMailable;

class MailablePayloadTest extends TestCase
{
    /** @test */
    public function it_can_render_a_mailable()
    {
        $mailable = new TestMailable();

        $payload = MailablePayload::forMailable($mailable);

        $this->assertTrue(is_string($payload->getContent()['html']));
    }
}
