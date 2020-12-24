<?php

namespace Spatie\LaravelRay;

use Illuminate\Console\Events\CommandStarting;
use Illuminate\Log\Events\MessageLogged;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Spatie\LaravelRay\DumpRecorder\DumpRecorder;
use Spatie\Ray\Client;
use Spatie\Ray\Payloads\Payload;
use Symfony\Component\Console\Output\OutputInterface;

class RayServiceProvider extends ServiceProvider
{
    protected ?OutputInterface $consoleOutput = null;

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
            ->registerBladeDirectives()
            ->listenForEvents();
    }

    protected function registerBindings(): self
    {
        $this->app->bind(Client::class, fn () => new Client(config('ray.port')), 'http://localhost');

        $this->app->bind(Ray::class, function () {
            $client = app(Client::class);

            $ray = new Ray($client);

            if (Ray::$enabled) {
                config('ray.enable_ray')
                    ? $ray->enable()
                    : $ray->disable();
            }
            $ray->setConsoleOutput($this->consoleOutput);

            return $ray;
        });

        $this->app->singleton(QueryLogger::class, fn () => new QueryLogger());

        Payload::$originFactoryClass = OriginFactory::class;

        $this->app->singleton(EventLogger::class, fn () => new EventLogger());

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
            $concernsMailable = $this->concernsLoggedMail($message->message);

            $concernsMailable
                ? $ray->loggedMail($message->message)
                : $ray->send($message->message);

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
        Collection::macro('ray', function (string $description = '') {
            $description === ''
                ? ray($this->items)
                : ray($description, $this->items);

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

    protected function listenForEvents(): self
    {
        Event::listen('*', function (string $event, array $arguments) {
            /** @var \Spatie\LaravelRay\EventLogger $eventLogger */
            $eventLogger = app(EventLogger::class);

            $eventLogger->handleEvent($event, $arguments);
        });

        Event::listen(CommandStarting::class, function (CommandStarting $event) {
            $this->consoleOutput = $event->output;
        });

        return $this;
    }

    protected function concernsLoggedMail(string $message): bool
    {
        if (! Str::startsWith($message, 'Message-ID')) {
            return false;
        }

        if (! Str::contains($message, '@swift.generated')) {
            return false;
        }

        return true;
    }
}
