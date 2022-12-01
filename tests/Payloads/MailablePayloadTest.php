<?php

use Spatie\LaravelRay\Payloads\MailablePayload;
use Spatie\LaravelRay\Tests\TestClasses\TestMailable;

it('can render a mailable', function () {
    $mailable = new TestMailable();

    $payload = MailablePayload::forMailable($mailable);

    expect(is_string($payload->getContent()['html']))->toBeTrue();
});
