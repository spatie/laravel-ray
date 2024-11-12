<?php

namespace Spatie\LaravelRay\Commands;

use Composer\InstalledVersions;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Process;
use Spatie\LaravelRay\Support\Composer;

class CleanRayCommand extends Command
{
    protected $signature = 'ray:clean';

    protected $description = 'Remove all Ray calls from your codebase.';

    public function handle(Filesystem $files)
    {
        $directories = [
            'app',
            'config',
            'database',
            'public',
            'resources',
            'routes',
            'tests',
        ];

        if (! InstalledVersions::isInstalled('rector/rector')) {
            (new Composer($files, defined('TESTBENCH_WORKING_PATH') ? TESTBENCH_WORKING_PATH : base_path()))
                ->requirePackages(['rector/rector'], true, $this->output);
        }

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
