<?php

namespace Spatie\LaravelRay;

use Composer\InstalledVersions;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Mail\Mailable;
use Illuminate\Testing\TestResponse;
use Spatie\LaravelRay\Payloads\ExceptionPayload;
use Spatie\LaravelRay\Payloads\LoggedMailPayload;
use Spatie\LaravelRay\Payloads\MailablePayload;
use Spatie\LaravelRay\Payloads\MarkdownPayload;
use Spatie\LaravelRay\Payloads\ModelPayload;
use Spatie\LaravelRay\Payloads\ResponsePayload;
use Spatie\LaravelRay\Watchers\EventLogger;
use Spatie\LaravelRay\Watchers\EventWatcher;
use Spatie\LaravelRay\Watchers\JobWatcher;
use Spatie\LaravelRay\Watchers\QueryWatcher;
use Spatie\Ray\Ray as BaseRay;

class Ray extends BaseRay
{
    /** @var bool */
    public static $enabled = true;

    public function enable(): self
    {
        self::$enabled = true;

        return $this;
    }

    public function disable(): self
    {
        self::$enabled = false;

        return $this;
    }

    public function enabled(): bool
    {
        return self::$enabled;
    }

    public function disabled(): bool
    {
        return ! self::$enabled;
    }

    public function loggedMail(string $loggedMail): self
    {
        $payload = LoggedMailPayload::forLoggedMail($loggedMail);

        $this->sendRequest($payload);

        return $this;
    }

    public function exception(Exception $exception): self
    {
        $payload = new ExceptionPayload($exception);

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

    public function showEvents($callable = null): self
    {
        $wasLoggingEvents = $this->eventWatcher()->enabled();

        $this->eventWatcher()->enable();

        if ($callable) {
            $callable();

            if (! $wasLoggingEvents) {
                $this->eventWatcher()->disable();
            }
        }

        return $this;
    }

    public function events($callable = null): self
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

    public function showJobs($callable = null): self
    {
        $wasLoggingJobs = $this->jobWatcher()->enabled();

        $this->jobWatcher()->enable();

        if ($callable) {
            $callable();

            if (! $wasLoggingJobs) {
                $this->jobWatcher()->disable();
            }
        }

        return $this;
    }

    public function jobs($callable = null): self
    {
        return $this->showJobs($callable);
    }

    public function stopShowingJobs(): self
    {
        $this->jobWatcher()->disable();

        return $this;
    }

    public function showQueries($callable = null): self
    {
        $wasLoggingQueries = $this->queryWatcher()->enabled();

        $this->queryWatcher()->enable();

        if (! is_null($callable)) {
            $callable();

            if (! $wasLoggingQueries) {
                $this->disable();
            }
        }

        return $this;
    }

    public function queries($callable = null): self
    {
        return $this->showQueries($callable);
    }

    public function stopShowingQueries(): self
    {
        $this->queryWatcher()->disable();

        return $this;
    }

    public function testResponse(TestResponse $testResponse)
    {
        $payload = ResponsePayload::fromTestResponse($testResponse);

        $this->sendRequest($payload);
    }

    protected function eventWatcher(): EventWatcher
    {
        return app(EventWatcher::class);
    }

    protected function jobWatcher(): JobWatcher
    {
        return app(JobWatcher::class);
    }

    protected function queryWatcher(): QueryWatcher
    {
        return app(QueryWatcher::class);
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
        if (! static::$enabled) {
            return $this;
        }

        $meta = [
            'laravel_version' => app()->version(),
        ];

        if (class_exists(InstalledVersions::class)) {
            $meta['laravel_ray_package_version'] = InstalledVersions::getVersion('spatie/laravel-ray');
        }

        return BaseRay::sendRequest($payloads, $meta);
    }
}
