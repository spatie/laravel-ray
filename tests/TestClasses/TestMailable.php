<?php

namespace Spatie\LaravelRay\Tests\TestClasses;

use Illuminate\Mail\Mailable;

class TestMailable extends Mailable
{
    public function build()
    {
        return $this->markdown('mails.test')
            ->attachData('file1', 'file_1.txt')
            ->attachData('file2', 'file_2.txt');
    }
}
