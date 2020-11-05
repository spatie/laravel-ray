<?php

namespace Spatie\LaravelTimber;

use Spatie\Timber\Origin\Origin;
use Spatie\Timber\Timber;

class OriginFactory
{
    public function getOrigin(): Origin
    {
        $frame = $this->getFrame();

        return new Origin(
            $frame['file'] ?? null,
            $frame['line'] ?? null,
        );
    }

    protected function getFrame(): ?array
    {
        $trace = array_reverse(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS));

        $frameIndex = $this->getIndexOfTimberCall($trace);

        if (! $frameIndex) {
            return null;
        }

        return $trace[$frameIndex - 1] ?? null;
    }

    protected function getIndexOfTimberCall(array $stackTrace): ?int
    {
        foreach ($stackTrace as $index => $frame) {
            if (($frame['class'] ?? '') === Timber::class) {
                return $index;
            }

            if ($this->startsWith($frame['file'], __DIR__)) {
                return $index;
            }
        }

        return null;
    }

    public function startsWith(string $hayStack, string $needle): bool
    {
        return strpos($hayStack, $needle) === 0;
    }
}
