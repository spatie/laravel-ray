<?php

use Illuminate\Support\Facades\Mail;
use Spatie\LaravelRay\Tests\TestClasses\TestMailable;
use Spatie\LaravelRay\Watchers\MailWatcher;

it('can send the mailable payload', function () {
    ray()->mailable(new TestMailable());

    expect($this->client->sentRequests())->toHaveCount(1);
});

it('can send a logged mailable', function () {
    Mail::mailer('log')
        ->cc(['adriaan' => 'adriaan@spatie.be', 'seb@spatie.be'])
        ->bcc(['willem@spatie.be', 'jef@spatie.be'])
        ->to(['freek@spatie.be', 'ruben@spatie.be'])
        ->send(new TestMailable());

    expect($this->client->sentRequests())->toHaveCount(1);
});

it('can send multiple mailable payloads', function () {
    ray()->mailable(new TestMailable(), new TestMailable());

    expect($this->client->sentPayloads())->toHaveCount(2);
    expect($this->client->sentRequests())->toHaveCount(1);
});

it('can automatically send mail to ray', function () {
    if (! MailWatcher::supportedByLaravelVersion()) {
        $this->markTestSkipped('Tests require Laravel 11.0.0 or greater.');
    }

    ray()->showMails();

    Mail::cc(['adriaan' => 'adriaan@spatie.be', 'seb@spatie.be'])
        ->bcc(['willem@spatie.be', 'jef@spatie.be'])
        ->to(['freek@spatie.be', 'ruben@spatie.be'])
        ->sendNow(new TestMailable());

    ray()->stopShowingMails();

    Mail::cc(['adriaan' => 'adriaan@spatie.be', 'seb@spatie.be'])
        ->bcc(['willem@spatie.be', 'jef@spatie.be'])
        ->to(['freek@spatie.be', 'ruben@spatie.be'])
        ->sendNow(new TestMailable());

    expect($this->client->sentRequests())->toHaveCount(3);
});
