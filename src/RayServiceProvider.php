<?php

namespace Spatie\LaravelRay;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Illuminate\Testing\TestResponse;
use Spatie\LaravelRay\Commands\PublishConfigCommand;
use Spatie\LaravelRay\Payloads\MailablePayload;
use Spatie\LaravelRay\Payloads\ModelPayload;
use Spatie\LaravelRay\Watchers\ApplicationLogWatcher;
use Spatie\LaravelRay\Watchers\CacheWatcher;
use Spatie\LaravelRay\Watchers\DumpWatcher;
use Spatie\LaravelRay\Watchers\EventWatcher;
use Spatie\LaravelRay\Watchers\ExceptionWatcher;
use Spatie\LaravelRay\Watchers\JobWatcher;
use Spatie\LaravelRay\Watchers\LoggedMailWatcher;
use Spatie\LaravelRay\Watchers\QueryWatcher;
use Spatie\LaravelRay\Watchers\RequestWatcher;
use Spatie\LaravelRay\Watchers\ViewWatcher;
use Spatie\Ray\Client;
use Spatie\Ray\PayloadFactory;
use Spatie\Ray\Payloads\Payload;
use Spatie\Ray\Settings\Settings;
use Spatie\Ray\Settings\SettingsFactory;

class RayServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this
            ->registerCommands()
            ->registerSettings()
            ->registerBindings()
            ->registerWatchers()
            ->registerMacros()
            ->registerBindings()
            ->registerBladeDirectives()
            ->registerPayloadFinder();
    }

    public function boot()
    {
        $this->bootWatchers();
    }

    protected function registerCommands(): self
    {
        $this->commands(PublishConfigCommand::class);

        return $this;
    }

    protected function registerSettings(): self
    {
        $this->app->singleton(Settings::class, function () {
            $settings = SettingsFactory::createFromConfigFile($this->app->configPath());

            return $settings->setDefaultSettings([
                'enable' => ! app()->environment('production'),
                'send_cache_to_ray' => false,
                'send_dumps_to_ray' => true,
                'send_jobs_to_ray' => false,
                'send_log_calls_to_ray' => true,
                'send_queries_to_ray' => false,
                'send_requests_to_ray' => false,
                'send_views_to_ray' => false,
            ]);
        });

        return $this;
    }

    protected function registerBindings(): self
    {
        $settings = app(Settings::class);

        $this->app->bind(Client::class, function () use ($settings) {
            return new Client($settings->port, $settings->host);
        });

        $this->app->bind(Ray::class, function () {
            $client = app(Client::class);

            $settings = app(Settings::class);

            $ray = new Ray($settings, $client);

            if (! $settings->enable) {
                $ray->disable();
            }

            return $ray;
        });

        Payload::$originFactoryClass = OriginFactory::class;

        return $this;
    }

    protected function registerWatchers(): self
    {
        $watchers = [
            ExceptionWatcher::class,
            LoggedMailWatcher::class,
            ApplicationLogWatcher::class,
            JobWatcher::class,
            EventWatcher::class,
            DumpWatcher::class,
            QueryWatcher::class,
            ViewWatcher::class,
            CacheWatcher::class,
            RequestWatcher::class,
        ];

        collect($watchers)
            ->each(function (string $watcherClass) {
                $this->app->singleton($watcherClass);
            });

        return $this;
    }

    protected function bootWatchers(): self
    {
        $watchers = [
            ExceptionWatcher::class,
            LoggedMailWatcher::class,
            ApplicationLogWatcher::class,
            JobWatcher::class,
            EventWatcher::class,
            DumpWatcher::class,
            QueryWatcher::class,
            ViewWatcher::class,
            CacheWatcher::class,
            RequestWatcher::class,
        ];

        collect($watchers)
            ->each(function (string $watcherClass) {
                /** @var \Spatie\LaravelRay\Watchers\Watcher $watcher */
                $watcher = app($watcherClass);

                $watcher->register();
            });

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
}
