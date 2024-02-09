<?php

namespace Spatie\LaravelRay\Commands;

use Composer\InstalledVersions;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;

class CleanRayCommand extends Command
{
    protected $signature = 'ray:clean';

    protected $description = 'Remove all Ray calls from your codebase.';

    public function handle()
    {
        if (
            ! InstalledVersions::isInstalled("rector/rector") ||
            version_compare(
                InstalledVersions::getPrettyVersion("rector/rector"),
                "1.0.0",
                "<"
            )
        ) {
            $this->error(
                'Rector is not installed or the version is not compatible with this package.'
                .' Please install rector version 1.0.0 or higher.'
            );

            return;
        }

        $directories = [
            'app',
            'config',
            'database',
            'public',
            'resources',
            'routes',
            'tests',
        ];

        $this->withProgressBar($directories, function ($directory) {
            $result = Process::run('./vendor/bin/remove-ray.sh ' . $directory);

            if (! $result->successful()) {
                $this->error($result->errorOutput());

                return;
            }
        });

        $this->newLine(2);
        $this->info('All Ray calls have been removed from your codebase.');
    }
}
