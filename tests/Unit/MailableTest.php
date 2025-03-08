<?php

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Mail;
use Spatie\LaravelRay\Tests\TestClasses\TestMailable;
use Spatie\LaravelRay\Watchers\MailWatcher;

it('can send the mailable payload', function () {
    ray()->mailable(new TestMailable());

    expect($this->client->sentRequests())->toHaveCount(1);
});

it('can send a logged mailable automatically', function () {
    Mail::mailer('log')
        ->cc(['adriaan' => 'adriaan@spatie.be', 'seb@spatie.be'])
        ->bcc(['willem@spatie.be', 'jef@spatie.be'])
        ->to(['freek@spatie.be', 'ruben@spatie.be'])
        ->send(new TestMailable());

    expect($this->client->sentRequests())->toHaveCount(2);
});

it('can send multiple mailable payloads', function () {
    ray()->mailable(new TestMailable(), new TestMailable());

    expect($this->client->sentPayloads())->toHaveCount(2);
    expect($this->client->sentRequests())->toHaveCount(1);
});

it('will automatically send mails to ray', function () {
    if (! (new MailWatcher())->supportsMessageSendingEvent()) {
        $this->markTestSkipped('This test works for Laravel versions that can automatically log all non-log mails');
    }

    // to addresses in to --> 2 mails will be sent
    Mail::cc(['adriaan' => 'adriaan@spatie.be', 'seb@spatie.be'])
        ->bcc(['willem@spatie.be', 'jef@spatie.be'])
        ->to(['freek@spatie.be', 'ruben@spatie.be'])
        ->send(new TestMailable());

    ray()->stopShowingMails();

    // these should not be logged in Ray
    Mail::cc(['adriaan' => 'adriaan@spatie.be', 'seb@spatie.be'])
        ->bcc(['willem@spatie.be', 'jef@spatie.be'])
        ->to(['freek@spatie.be', 'ruben@spatie.be'])
        ->send(new TestMailable());

    $requests = $this->client->sentRequests();

    expect($requests)->toHaveCount(2);
    expect(Arr::get($requests, '0.payloads.0.origin.file'))->toContain('Mailer.php');
});
