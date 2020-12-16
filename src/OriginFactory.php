<?php

namespace Spatie\LaravelRay;

use Illuminate\Events\Dispatcher;
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
        var_dump(Backtrace::create()->frames());
        return collect(Backtrace::create()->frames())
            ->first(function(Frame $frame) {
                echo $frame->class . PHP_EOL;
                if ($frame->file === 'unknown') {
                    return false;
                }

                if (Str::endsWith($frame->file, 'laravel-ray/src/helpers.php')) {
                    return false;
                }

                if (Str::startsWith($frame->class, 'Illuminate\Database')) {
                    return false;
                }

                if ($frame->class === Dispatcher::class) {
                    return false;
                }

                if (Str::startsWith($frame->class, ['Spatie\LaravelRay', 'Spatie\Ray'])) {
                    return false;
                }

                return true;
            });
    }
}
