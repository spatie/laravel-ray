<?php

namespace Spatie\LaravelTimber;

use Illuminate\Log\Events\MessageLogged;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Spatie\Timber\Client;

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
            ->defineTimberFunction();
    }

    protected function registerBindings(): self
    {
        $this->app->bind(Timber::class, function () {
            $timberConfig = config('timber');

            $client = new Client("http://localhost:{$timberConfig['port']}");

            return new Timber($client);
        });

        $this->app->singleton(QueryLogger::class, function () {
            return new QueryLogger();
        });

        return $this;
    }

    protected function listenForLogEvents(): self
    {
        if (! config('timber.send_log_calls_to_timber')) {
            return $this;
        }

        Event::listen(MessageLogged::class, function (MessageLogged $message) {
            /** @var Timber $timber */
            $timber = app(Timber::class);

            $timber->send($message);

            if ($message->level === 'error') {
                $timber->color('red');
            }

            if ($message->level === 'warning') {
                $timber->color('orange');
            }
        });

        return $this;
    }

    protected function defineTimberFunction(): self
    {
        function timber()
        {
            return app(Timber::class);
        }

        return $this;
    }


}
