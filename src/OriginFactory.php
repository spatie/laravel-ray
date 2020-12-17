<?php

namespace Spatie\LaravelRay;

use Illuminate\Events\Dispatcher;
use Illuminate\Log\Logger;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Spatie\Backtrace\Backtrace;
use Spatie\Backtrace\Frame;
use Spatie\LaravelRay\DumpRecorder\DumpRecorder;
use Spatie\Ray\Origin\Origin;
use Spatie\Ray\Ray;
use Symfony\Component\VarDumper\VarDumper;

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

        /** @var Frame|null $foundFrame */
        $foundFrame = $frames[$indexOfRay] ?? null;

        /** @var Frame|null $foundFrame */
        $oneFrameAboveFoundFrame = $frames[$indexOfRay + 1] ?? null;


        if (! $foundFrame) {
            return null;
        }

        if ($foundFrame->class === QueryLogger::class) {
            return $this->findFrameForQuery($frames);
        }

        if ($foundFrame->class === DumpRecorder::class) {
            return $this->findFrameForDump($frames);
        }

        if ($oneFrameAboveFoundFrame->class === Dispatcher::class) {
            return $this->findFrameForLog($frames);
        }

        return $oneFrameAboveFoundFrame;
    }

    protected function findFrameForQuery(Collection $frames): ?Frame
    {
        $indexOfLastDatabaseCall = $frames
            ->search(fn(Frame $frame) => Str::startsWith($frame->class, 'Illuminate\Database'));

        return $frames[$indexOfLastDatabaseCall + 1] ?? null;
    }

    protected function findFrameForDump(Collection $frames): ?Frame
    {
        $indexOfDumpCall = $frames
            ->search(function(Frame $frame) {
                if (! is_null($frame->class)) {
                    return false;
                }

                return in_array($frame->method, ['dump', 'dd']);
            });

        return $frames[$indexOfDumpCall + 1] ?? null;
    }

    protected function findFrameForLog(Collection $frames): ?Frame
    {
        $indexOfLoggerCall = $frames
            ->search(function(Frame $frame) {
                return $frame->class === Logger::class;
            });

        return $frames[$indexOfLoggerCall + 1] ?? null;
    }
}
