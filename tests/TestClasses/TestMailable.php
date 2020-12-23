<?php

namespace Spatie\LaravelRay\Tests\TestClasses;

use Illuminate\Mail\Mailable;

class TestMailable extends Mailable
{
    public function build()
    {
        return $this->markdown('mails.test');
    }
}
