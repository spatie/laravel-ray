<?php

namespace Spatie\LaravelRay\Tests\TestClasses;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;

class TestMailable extends Mailable
{
    public function build()
    {
        return $this->markdown('mails.test');
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [
            Attachment::fromData(fn () => 'file1')->as('file_1.txt')->withMime('text/plain'),
            Attachment::fromData(fn () => 'file2')->as('file_2.txt')->withMime('text/plain'),
        ];
    }
}
