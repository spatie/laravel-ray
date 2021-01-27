<?php

namespace Spatie\LaravelRay\DumpRecorder;

use Closure;
use Illuminate\Contracts\Container\Container;
use Spatie\LaravelRay\Ray;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\CliDumper;
use Symfony\Component\VarDumper\Dumper\HtmlDumper as BaseHtmlDumper;
use Symfony\Component\VarDumper\VarDumper;

class DumpRecorder
{
    /** @var array */
    protected $dumps = [];

    /** @var \Illuminate\Contracts\Container\Container */
    protected $app;

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

        VarDumper::setHandler(function ($dumpedVariable) use ($multiDumpHandler) {
            $multiDumpHandler->dump($dumpedVariable);
        });

        $multiDumpHandler
            ->addHandler($this->getDefaultHandler())
            ->addHandler(function ($dumpedVariable) {
                return app(Ray::class)->send($dumpedVariable);
            });

        return $this;
    }

    protected function getDefaultHandler(): Closure
    {
        return function ($value) {
            $data = (new VarCloner)->cloneVar($value);

            $this->getDumper()->dump($data);
        };
    }

    protected function getDumper()
    {
        if (isset($_SERVER['VAR_DUMPER_FORMAT'])) {
            if ($_SERVER['VAR_DUMPER_FORMAT'] === 'html') {
                return new BaseHtmlDumper();
            }

            return new CliDumper();
        }

        if (in_array(PHP_SAPI, ['cli', 'phpdbg'])) {
            return new CliDumper() ;
        }

        return new BaseHtmlDumper();
    }
}
