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
use Spatie\Ray\Payloads\ApplicationLogPayload;
use Spatie\Ray\Payloads\Payload;
use Spatie\Ray\Settings\Settings;
use Spatie\Ray\Settings\SettingsFactory;

class RayServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this
            ->registerSettings()
            ->registerBindings()
            ->listenForLogEvents()
            ->listenForDumps()
            ->registerMacros()
            ->registerBindings()
            ->registerBladeDirectives()
            ->listenForEvents();
    }

    protected function registerSettings(): self
    {
        $this->app->singleton(Settings::class, function () {
            $settings = SettingsFactory::createFromConfigFile($this->app->configPath());

            return $settings->setDefaultSettings([
                'enable' => ! app()->environment('production'),
                'send_log_calls_to_ray' => true,
                'send_dumps_to_ray' => true,
            ]);
        });

        return $this;
    }

    protected function registerBindings(): self
    {
        $settings = app(Settings::class);

        $this->app->bind(Client::class, fn () => new Client($settings->port, $settings->host));

        $this->app->bind(Ray::class, function () {
            $client = app(Client::class);

            $settings = app(Settings::class);

            $ray = new Ray($settings, $client);

            if (! $settings->enable) {
                $ray->disable();
            }

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

            /** @var Ray $ray */
            $ray = app(Ray::class);

            if (! $ray->settings->send_log_calls_to_ray) {
                return $this;
            }

            $concernsMailable = $this->concernsLoggedMail($message->message);

            if ($concernsMailable) {
                $ray->loggedMail($message->message);

                return $this;
            }

            $payload = new ApplicationLogPayload($message->message);

            $ray->sendRequest($payload);

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
        $settings = app(Settings::class);

        if (! $settings->send_dumps_to_ray) {
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
        if ($this->app->has('blade.compiler')) {
            Blade::directive('ray', function ($expression) {
                return "<?php ray($expression); ?>";
            });
        }

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
