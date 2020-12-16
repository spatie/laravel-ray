<?php

namespace Spatie\LaravelRay;

use Illuminate\Support\Str;
use Spatie\Backtrace\Backtrace;
use Spatie\Backtrace\Frame;
use Spatie\Ray\Origin\Origin;
use Spatie\Ray\Ray;

class OriginFactory
{
    public function getOrigin(): Origin
    {
        $frame = $this->getFrame();

        return new Origin(
            optional($frame)->file,
            optional($frame)->lineNumber,
        );
    }

    protected function getFrame(): Frame
    {
        return collect(Backtrace::create()->frames())
            ->first(function(Frame $frame) {
                if ($frame->file === 'unknown') {
                    return false;
                }

                if (Str::endsWith($frame->file, 'laravel-ray/src/helpers.php')) {
                    return false;
                }

                return ! Str::startsWith($frame->class, ['Spatie\LaravelRay', 'Spatie\Ray']);
            });
    }
}
