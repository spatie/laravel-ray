<?php

namespace Spatie\LaravelTimber\DumpRecorder;

use Closure;
use Illuminate\Contracts\Foundation\Application;
use Spatie\LaravelTimber\Timber;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\CliDumper;
use Symfony\Component\VarDumper\Dumper\HtmlDumper as BaseHtmlDumper;
use Symfony\Component\VarDumper\VarDumper;

class DumpRecorder
{
    protected array $dumps = [];

    protected Application $app;

    public function __construct(Application $app)
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
                app(Timber::class)->send($dumpedVariable);
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
