<?php

namespace Spatie\LaravelTimber;

use Illuminate\Log\Events\MessageLogged;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Spatie\LaravelTimber\DumpRecorder\DumpRecorder;
use Spatie\Timber\Client;
use Spatie\Timber\Payloads\Payload;

class TimberServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/timber.php' => config_path('timber.php'),
            ], 'config');
        }
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/timber.php', 'timber');

        $this
            ->registerBindings()
            ->listenForLogEvents()
            ->listenForDumps()
            ->registerMacros()
            ->registerBindings()
            ->registerBladeDirectives();
    }

    protected function registerBindings(): self
    {
        $this->app->bind(Client::class, fn () => new Client('http://localhost', config('timber.port')));

        $this->app->bind(Timber::class, function () {
            $client = app(Client::class);

            return new Timber($client);
        });

        $this->app->singleton(QueryLogger::class, fn () => new QueryLogger());

        Payload::$originFactoryClass = OriginFactory::class;

        return $this;
    }

    protected function listenForLogEvents(): self
    {
        Event::listen(MessageLogged::class, function (MessageLogged $message) {
            if (! config('timber.send_log_calls_to_timber')) {
                return $this;
            }

            /** @var Timber $timber */
            $timber = app(Timber::class);

            $timber->send($message->message);

            if ($message->level === 'error') {
                $timber->color('red');
            }

            if ($message->level === 'warning') {
                $timber->color('orange');
            }
        });

        return $this;
    }

    protected function listenForDumps(): self
    {
        if (! config('timber.send_dumps_to_timber')) {
            return $this;
        }

        $this->app->make(DumpRecorder::class)->register();

        return $this;
    }

    protected function registerMacros(): self
    {
        Collection::macro('timber', function () {
            timber($this->items);

            return $this;
        });

        return $this;
    }

    protected function registerBladeDirectives(): self
    {
        Blade::directive('timber', function ($expression) {
            return "<?php timber($expression); ?>";
        });

        return $this;
    }
}
