<?php

namespace Spatie\LaravelRay\Tests\TestClasses;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TestMailable extends Mailable
{
    public function build()
    {
        return $this->markdown('mails.test');
    }
}
