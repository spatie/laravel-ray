<?php

namespace Spatie\LaravelRay\Support;

use Closure;
use Symfony\Component\Console\Output\OutputInterface;

class Composer extends \Illuminate\Support\Composer
{
    /**
     * Install the given Composer packages into the application.
     *
     * Override this method for `illuminate/support` 10 and below.
     *
     * @param  array<int, string>  $packages
     * @param  string|null  $composerBinary
     * @return bool
     */
    public function requirePackages(array $packages, bool $dev = false, Closure|OutputInterface|null $output = null, $composerBinary = null)
    {
        $command = collect([
            ...$this->findComposer($composerBinary),
            'require',
            ...$packages,
        ])
            ->when($dev, function ($command) {
                $command->push('--dev');
            })->all();

        return $this->getProcess($command, ['COMPOSER_MEMORY_LIMIT' => '-1'])
            ->run(
                $output instanceof OutputInterface
                    ? function ($type, $line) use ($output) {
                        $output->write('    '.$line);
                    } : $output
            ) === 0;
    }
}
