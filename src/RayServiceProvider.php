<?php

namespace Spatie\LaravelRay;

use Illuminate\Log\Events\MessageLogged;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Spatie\LaravelRay\DumpRecorder\DumpRecorder;
use Spatie\Ray\Client;
use Spatie\Ray\Payloads\Payload;

class RayServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/ray.php' => config_path('ray.php'),
            ], 'config');
        }
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/ray.php', 'ray');

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
        $this->app->bind(Client::class, fn () => new Client('http://localhost', config('ray.port')));

        $this->app->bind(Ray::class, function () {
            $client = app(Client::class);

            return new Ray($client);
        });

        $this->app->singleton(QueryLogger::class, fn () => new QueryLogger());

        Payload::$originFactoryClass = OriginFactory::class;

        return $this;
    }

    protected function listenForLogEvents(): self
    {
        Event::listen(MessageLogged::class, function (MessageLogged $message) {
            if (! config('ray.send_log_calls_to_ray')) {
                return $this;
            }

            /** @var Ray $ray */
            $ray = app(Ray::class);

            $ray->send($message->message);

            if ($message->level === 'error') {
                $ray->color('red');
            }

            if ($message->level === 'warning') {
                $ray->color('orange');
            }
        });

        return $this;
    }

    protected function listenForDumps(): self
    {
        if (! config('ray.send_dumps_to_ray')) {
            return $this;
        }

        $this->app->make(DumpRecorder::class)->register();

        return $this;
    }

    protected function registerMacros(): self
    {
        Collection::macro('ray', function () {
            ray($this->items);

            return $this;
        });

        return $this;
    }

    protected function registerBladeDirectives(): self
    {
        Blade::directive('ray', function ($expression) {
            return "<?php ray($expression); ?>";
        });

        return $this;
    }
}
