<?php

namespace Spatie\Timber\Commands;

use Illuminate\Console\Command;

class TimberCommand extends Command
{
    public $signature = 'laravel-timber';

    public $description = 'My command';

    public function handle()
    {
        $this->comment('All done');
    }
}
