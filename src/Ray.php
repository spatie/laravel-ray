<?php

namespace Spatie\LaravelRay;

use Closure;
use Composer\InstalledVersions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Mail\Mailable;
use Illuminate\Testing\TestResponse;
use Illuminate\View\View;
use Spatie\LaravelRay\Payloads\LoggedMailPayload;
use Spatie\LaravelRay\Payloads\MailablePayload;
use Spatie\LaravelRay\Payloads\MarkdownPayload;
use Spatie\LaravelRay\Payloads\ModelPayload;
use Spatie\LaravelRay\Payloads\ResponsePayload;
use Spatie\LaravelRay\Payloads\ViewPayload;
use Spatie\LaravelRay\Watchers\CacheWatcher;
use Spatie\LaravelRay\Watchers\EventWatcher;
use Spatie\LaravelRay\Watchers\JobWatcher;
use Spatie\LaravelRay\Watchers\QueryWatcher;
use Spatie\LaravelRay\Watchers\RequestWatcher;
use Spatie\LaravelRay\Watchers\ViewWatcher;
use Spatie\LaravelRay\Watchers\Watcher;
use Spatie\Ray\Client;
use Spatie\Ray\Ray as BaseRay;
use Spatie\Ray\Settings\Settings;

class Ray extends BaseRay
{
    public function __construct(Settings $settings, Client $client = null, string $uuid = null)
    {
        // persist the enabled setting across multiple instantiations
        $enabled = static::$enabled;

        parent::__construct($settings, $client, $uuid);

        static::$enabled = $enabled;
    }

    public function loggedMail(string $loggedMail): self
    {
        $payload = LoggedMailPayload::forLoggedMail($loggedMail);

        $this->sendRequest($payload);

        return $this;
    }

    public function mailable(Mailable $mailable): self
    {
        $payload = MailablePayload::forMailable($mailable);

        $this->sendRequest($payload);

        return $this;
    }

    /**
     * @param Model|iterable ...$models
     *
     * @return \Spatie\LaravelRay\Ray
     */
    public function model(...$model): self
    {
        $models = [];
        foreach ($model as $passedModel) {
            if (is_null($passedModel)) {
                $models[] = null;

                continue;
            }
            if ($passedModel instanceof Model) {
                $models[] = $passedModel;

                continue;
            }

            if (is_iterable($model)) {
                foreach ($passedModel as $item) {
                    $models[] = $item;

                    continue;
                }
            }
        }

        $payloads = array_map(function (?Model $model) {
            return new ModelPayload($model);
        }, $models);

        foreach ($payloads as $payload) {
            ray()->sendRequest($payload);
        }

        return $this;
    }

    /**
     * @param Model|iterable $models
     *
     * @return \Spatie\LaravelRay\Ray
     */
    public function models($models): self
    {
        return $this->model($models);
    }

    public function markdown(string $markdown): self
    {
        $payload = new MarkdownPayload($markdown);

        $this->sendRequest($payload);

        return $this;
    }

    /**
     * @param null $callable
     *
     * @return \Spatie\LaravelRay\Ray
     */
    public function showEvents($callable = null)
    {
        $watcher = app(EventWatcher::class);

        return $this->handleWatcherCallable($watcher, $callable);
    }

    public function events($callable = null)
    {
        return $this->showEvents($callable);
    }

    public function stopShowingEvents(): self
    {
        /** @var \Spatie\LaravelRay\Watchers\EventWatcher $eventWatcher */
        $eventWatcher = app(EventWatcher::class);

        $eventWatcher->disable();

        return $this;
    }

    /**
     * @param null $callable
     *
     * @return \Spatie\LaravelRay\Ray
     */
    public function showJobs($callable = null)
    {
        $watcher = app(JobWatcher::class);

        return $this->handleWatcherCallable($watcher, $callable);
    }

    /**
     * @param null $callable
     *
     * @return \Spatie\LaravelRay\Ray
     */
    public function showCache($callable = null)
    {
        $watcher = app(CacheWatcher::class);

        return $this->handleWatcherCallable($watcher, $callable);
    }

    public function stopShowingCache(): self
    {
        app(CacheWatcher::class)->disable();

        return $this;
    }

    public function jobs($callable = null)
    {
        return $this->showJobs($callable);
    }

    public function stopShowingJobs(): self
    {
        app(JobWatcher::class)->disable();

        return $this;
    }

    public function view(View $view): self
    {
        $payload = new ViewPayload($view);

        return $this->sendRequest($payload);
    }

    /**
     * @param null $callable
     *
     * @return \Spatie\LaravelRay\Ray
     */
    public function showViews($callable = null)
    {
        $watcher = app(ViewWatcher::class);

        return $this->handleWatcherCallable($watcher, $callable);
    }

    public function views($callable = null)
    {
        return $this->showViews($callable);
    }

    public function stopShowingViews(): self
    {
        app(ViewWatcher::class)->disable();

        return $this;
    }

    /**
     * @param null $callable
     *
     * @return \Spatie\LaravelRay\Ray
     */
    public function showQueries($callable = null)
    {
        $watcher = app(QueryWatcher::class);

        return $this->handleWatcherCallable($watcher, $callable);
    }

    public function queries($callable = null)
    {
        return $this->showQueries($callable);
    }

    public function stopShowingQueries(): self
    {
        app(QueryWatcher::class)->disable();

        return $this;
    }

    /**
     * @param null $callable
     *
     * @return \Spatie\LaravelRay\Ray
     */
    public function showRequests($callable = null)
    {
        $watcher = app(RequestWatcher::class);

        return $this->handleWatcherCallable($watcher, $callable);
    }

    public function requests($callable = null)
    {
        return $this->showRequests($callable);
    }

    public function stopShowingRequests(): self
    {
        $this->requestWatcher()->disable();

        return $this;
    }

    protected function handleWatcherCallable(Watcher $watcher, Closure $callable = null): RayProxy
    {
        $rayProxy = new RayProxy();

        $wasEnabled = $watcher->enabled();

        $watcher->enable();

        if ($rayProxy) {
            $watcher->setRayProxy($rayProxy);
        }

        if ($callable) {
            $callable();

            if (! $wasEnabled) {
                $watcher->disable();
            }
        }

        return $rayProxy;
    }

    public function testResponse(TestResponse $testResponse)
    {
        $payload = ResponsePayload::fromTestResponse($testResponse);

        $this->sendRequest($payload);
    }

    protected function requestWatcher(): RequestWatcher
    {
        return app(RequestWatcher::class);
    }

    /**
     * @param \Spatie\Ray\Payloads\Payload|\Spatie\Ray\Payloads\Payload[] $payloads
     * @param array $meta
     *
     * @return \Spatie\Ray\Ray
     * @throws \Exception
     */
    public function sendRequest($payloads, array $meta = []): BaseRay
    {
        if (! $this->enabled()) {
            return $this;
        }

        $meta = [
            'laravel_version' => app()->version(),
        ];

        if (class_exists(InstalledVersions::class)) {
            try {
                $meta['laravel_ray_package_version'] = InstalledVersions::getVersion('spatie/laravel-ray');
            } catch (\Exception $e) {
                $meta['laravel_ray_package_version'] = '0.0.0';
            }
        }

        return BaseRay::sendRequest($payloads, $meta);
    }
}
