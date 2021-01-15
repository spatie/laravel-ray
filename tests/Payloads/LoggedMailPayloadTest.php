<?php

namespace Spatie\LaravelRay\Tests\Payloads;

use Spatie\LaravelRay\Payloads\LoggedMailPayload;
use Spatie\LaravelRay\Tests\TestCase;

class LoggedMailPayloadTest extends TestCase
{
    /** @test */
    public function it_can_parse_a_logged_mail()
    {
        $loggedMail = <<<EOD
Message-ID: <780b20b2a80adefb6ebb6c9fb7d15d8a@swift.generated>
Date: Fri, 15 Jan 2021 08:54:24 +0000
Subject: Test Mailable
From: Example <hello@example.com>
To: Freek <freek@spatie.be>, ruben@spatie.be
Cc: adriaan@spatie.be, Seb <seb@spatie.be>
Bcc: willem@spatie.be
MIME-Version: 1.0
Content-Type: multipart/alternative;

# fake mail
EOD;

        $payload = LoggedMailPayload::forLoggedMail($loggedMail);

        $this->assertEquals([
            'html' => '# fake mail',
            'subject' => 'Test Mailable',
            'from' => [
                [
                    'name' => 'Example',
                    'email' => 'hello@example.com',
                ],
            ],
            'to' => [
                [
                    'name' => 'Freek',
                    'email' => 'freek@spatie.be',
                ],
                [
                    'name' => '',
                    'email' => 'ruben@spatie.be',
                ],
            ],
            'cc' => [
                [
                    'name' => '',
                    'email' => 'adriaan@spatie.be',
                ],
                [
                    'name' => 'Seb',
                    'email' => 'seb@spatie.be',
                ],
            ],
            'bcc' => [
                [
                    'name' => '',
                    'email' => 'willem@spatie.be',
                ],

            ],
        ], $payload->getContent());
    }

    /** @test */
    public function it_can_omit_some_headers_in_a_parsed_mail()
    {
        $loggedMail = <<<EOD
From: Example <hello@example.com>
To: Freek <freek@spatie.be>
Content-Type: multipart/alternative;

# fake mail
EOD;

        $payload = LoggedMailPayload::forLoggedMail($loggedMail);

        $this->assertEquals([
            'html' => '# fake mail',
            'subject' => null,
            'from' => [
                [
                    'name' => 'Example',
                    'email' => 'hello@example.com',
                ],
            ],
            'to' => [
                [
                    'name' => 'Freek',
                    'email' => 'freek@spatie.be',
                ],
            ],
            'cc' => [],
            'bcc' => [],
        ], $payload->getContent());
    }
}
