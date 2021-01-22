<?php

namespace Spatie\LaravelRay;

use Illuminate\Console\Events\CommandStarting;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Log\Events\MessageLogged;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Queue\Events\JobQueued;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use Spatie\LaravelRay\Commands\PublishConfigCommand;
use Spatie\LaravelRay\DumpRecorder\DumpRecorder;
use Spatie\LaravelRay\Payloads\MailablePayload;
use Spatie\LaravelRay\Payloads\ModelPayload;
use Spatie\Ray\Client;
use Spatie\Ray\PayloadFactory;
use Spatie\Ray\Payloads\ApplicationLogPayload;
use Spatie\Ray\Payloads\Payload;
use Spatie\Ray\Settings\Settings;
use Spatie\Ray\Settings\SettingsFactory;

class RayServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                PublishConfigCommand::class,
            ]);
        }
    }

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
            ->registerPayloadFinder()
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
        $this->app->singleton(JobLogger::class, fn () => new JobLogger());


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

        TestResponse::macro('ray', function () {
            ray()->testResponse($this);

            return $this;
        });

        return $this;
    }

    protected function registerBladeDirectives(): self
    {
        if (! $this->app->has('blade.compiler')) {
            return $this;
        }

        Blade::directive('ray', function ($expression) {
            return "<?php ray($expression); ?>";
        });

        return $this;
    }

    protected function registerPayloadFinder(): self
    {
        PayloadFactory::registerPayloadFinder(function ($argument) {
            if ($argument instanceof Model) {
                return new ModelPayload($argument);
            }

            if ($argument instanceof Mailable) {
                return MailablePayload::forMailable($argument);
            }

            return null;
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

        Event::listen([
            JobQueued::class,
            JobProcessing::class,
            JobProcessed::class,
            JobFailed::class,
        ], function (object $event) {
            /** @var \Spatie\LaravelRay\JobLogger $jobLogger */
            $jobLogger = app(JobLogger::class);

            if (! $jobLogger->isLoggingJobs()) {
                return;
            }

            $jobLogger->handleJobEvent($event);
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

        if (! Str::contains($message, 'swift')) {
            return false;
        }

        return true;
    }
}
