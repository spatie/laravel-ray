<?php

namespace Spatie\LaravelRay\DumpRecorder;

use Illuminate\Contracts\Container\Container;
use Spatie\LaravelRay\Ray;
use Symfony\Component\VarDumper\VarDumper;

class DumpRecorder
{
    /** @var array */
    protected $dumps = [];

    /** @var \Illuminate\Contracts\Container\Container */
    protected $app;

    protected static $registeredHandler = false;

    public function __construct(Container $app)
    {
        $this->app = $app;
    }

    public function register(): self
    {
        $multiDumpHandler = new MultiDumpHandler();

        $this->app->singleton(MultiDumpHandler::class, function () use ($multiDumpHandler) {
            return $multiDumpHandler;
        });

        $handler = function ($dumpedVariable) use ($multiDumpHandler) {
            if ($this->shouldDump()) {
                $multiDumpHandler->dump($dumpedVariable);
            }
        };

        if (! static::$registeredHandler) {
            static::$registeredHandler = true;

            $originalHandler = VarDumper::setHandler($handler);

            if ($originalHandler) {
                $multiDumpHandler->addHandler($originalHandler);
            }

            $multiDumpHandler->addHandler(function ($dumpedVariable) {
                return app(Ray::class)->send($dumpedVariable);
            });
        }

        return $this;
    }

    protected function shouldDump(): bool
    {
        /** @var Ray $ray */
        $ray = app(Ray::class);

        return $ray->settings->send_dumps_to_ray;
    }
}
