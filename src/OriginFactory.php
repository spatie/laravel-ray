<?php

namespace Spatie\LaravelRay;

use Illuminate\Events\Dispatcher;
use Illuminate\Log\Logger;
use Illuminate\Log\LogManager;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Str;
use Spatie\Backtrace\Backtrace;
use Spatie\Backtrace\Frame;
use Spatie\LaravelRay\DumpRecorder\DumpRecorder;
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

    protected function getFrame(): ?Frame
    {
        $frames = collect(Backtrace::create()->frames())->reverse();

        $indexOfRay = $frames
            ->search(function (Frame $frame) {
                if ($frame->class === Ray::class) {
                    return true;
                }

                if (Str::startsWith($frame->file, __DIR__)) {
                    return true;
                }

                return false;
            });

        /** @var Frame|null $rayFrame */
        $rayFrame = $frames[$indexOfRay] ?? null;

        $rayFunctionFrame = $frames[$indexOfRay + 2] ?? null;

        /** @var Frame|null $foundFrame */
        $originFrame = $frames[$indexOfRay + 1] ?? null;

        if ($originFrame && Str::endsWith($originFrame->file, Ray::makePathOsSafe('ray/src/helpers.php'))) {
            $framesAbove = 2;

            if ($rayFunctionFrame && $rayFunctionFrame->method === 'rd') {
                $framesAbove = 3;
            }

            $originFrame = $frames[$indexOfRay + $framesAbove] ?? null;
        }

        if (! $rayFrame) {
            return null;
        }

        if ($rayFrame->class === Collection::class && Str::startsWith($rayFrame->method, 'Spatie\LaravelRay')) {
            return $this->findFrameForCollectionMacro($frames, $indexOfRay);
        }

        if ($rayFrame->class === QueryLogger::class) {
            return $this->findFrameForQuery($frames);
        }

        if ($rayFrame->class === DumpRecorder::class) {
            return $this->findFrameForDump($frames);
        }

        if ($originFrame->class === Dispatcher::class) {
            return $this->findFrameForEvent($frames);
        }

        if (Str::endsWith($originFrame->file, Ray::makePathOsSafe('/vendor/psy/psysh/src/ExecutionLoopClosure.php'))) {
            $this->returnTinkerFrame();
        }

        if (Str::startsWith($originFrame->file, storage_path('framework/views'))) {
            return $this->replaceCompiledViewPathWithOriginalViewPath($originFrame);
        }

        return $originFrame;
    }

    protected function findFrameForCollectionMacro(Collection $frames, int $indexOfFoundFrame): ?Frame
    {
        return $frames[$indexOfFoundFrame + 2];
    }

    protected function findFrameForQuery(Collection $frames): ?Frame
    {
        $indexOfLastDatabaseCall = $frames
            ->search(fn (Frame $frame) => Str::startsWith($frame->class, 'Illuminate\Database'));

        return $frames[$indexOfLastDatabaseCall + 1] ?? null;
    }

    protected function findFrameForDump(Collection $frames): ?Frame
    {
        $indexOfDumpCall = $frames
            ->search(function (Frame $frame) {
                if (! is_null($frame->class)) {
                    return false;
                }

                return in_array($frame->method, ['dump', 'dd']);
            });

        return $frames[$indexOfDumpCall + 1] ?? null;
    }

    protected function findFrameForEvent(Collection $frames): ?Frame
    {
        $indexOfLoggerCall = $frames
            ->search(function (Frame $frame) {
                return $frame->class === Logger::class;
            });

        if ($indexOfLoggerCall) {
            return $this->findFrameForLog($frames, $indexOfLoggerCall);
        }

        $indexOfEventDispatcherCall = $frames
            ->search(function (Frame $frame) {
                return ($frame->class === Dispatcher::class) && $frame->method === 'dispatch';
            });


        /** @var Frame $foundFrame */
        if ($foundFrame = $frames[$indexOfEventDispatcherCall + 2]) {
            if (Str::endsWith($foundFrame->file, Ray::makePathOsSafe('/Illuminate/Foundation/Events/Dispatchable.php'))) {
                $foundFrame = $frames[$indexOfEventDispatcherCall + 3];
            }
        };

        return $foundFrame ?? null;
    }

    protected function findFrameForLog(Collection $frames, int $indexOfLoggerCall): ?Frame
    {
        /** @var Frame $foundFrame */
        if ($foundFrame = $frames[$indexOfLoggerCall + 1]) {
            if ($foundFrame->class === LogManager::class) {
                $foundFrame = $frames[$indexOfLoggerCall + 2];

                if ($foundFrame->class = Facade::class) {
                    $foundFrame = $frames[$indexOfLoggerCall + 3];
                }

                if (Str::endsWith($foundFrame->file, Ray::makePathOsSafe('/Illuminate/Foundation/helpers.php'))) {
                    $foundFrame = $frames[$indexOfLoggerCall + 3];
                }
            }
        }

        return $foundFrame ?? null;
    }

    protected function replaceCompiledViewPathWithOriginalViewPath(Frame $frame): Frame
    {
        if (! file_exists($frame->file)) {
            return $frame;
        }

        $fileContents = file_get_contents($frame->file);

        $originalViewPath = trim(Str::between($fileContents, '/**PATH', 'ENDPATH**/'));

        if (! file_exists($originalViewPath)) {
            return $frame;
        }

        $frame->file = $originalViewPath;
        $frame->lineNumber = 1;

        return $frame;
    }
}
