<?php

namespace Spatie\LaravelRay;

use Composer\InstalledVersions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Mail\Mailable;
use Illuminate\Testing\TestResponse;
use Spatie\LaravelRay\Payloads\LoggedMailPayload;
use Spatie\LaravelRay\Payloads\MailablePayload;
use Spatie\LaravelRay\Payloads\MarkdownPayload;
use Spatie\LaravelRay\Payloads\ModelPayload;
use Spatie\LaravelRay\Payloads\ResponsePayload;
use Spatie\Ray\Ray as BaseRay;

class Ray extends BaseRay
{
    public static bool $enabled = true;

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
        $wasLoggingEvents = $this->eventLogger()->isLoggingEvents();

        $this->eventLogger()->enable();

        if ($callable) {
            $callable();

            if (! $wasLoggingEvents) {
                $this->eventLogger()->disable();
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
        /** @var \Spatie\LaravelRay\EventLogger $eventLogger */
        $eventLogger = app(EventLogger::class);

        $eventLogger->disable();

        return $this;
    }

    public function showJobs($callable = null): self
    {
        $wasLoggingJobs = $this->jobLogger()->isLoggingJobs();

        $this->jobLogger()->enable();

        if ($callable) {
            $callable();

            if (! $wasLoggingJobs) {
                $this->jobLogger()->disable();
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
        /** @var \Spatie\LaravelRay\JobLogger $jobLogger */
        $jobLogger = app(JobLogger::class);

        $jobLogger->disable();

        return $this;
    }

    public function showQueries($callable = null): self
    {
        $wasLoggingQueries = $this->queryLogger()->isLoggingQueries();

        $this->queryLogger()->startLoggingQueries();

        if (! is_null($callable)) {
            $callable();

            if (! $wasLoggingQueries) {
                $this->stopShowingQueries();
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
        $this->queryLogger()->stopLoggingQueries();

        return $this;
    }

    public function testResponse(TestResponse $testResponse)
    {
        $payload = ResponsePayload::fromTestResponse($testResponse);

        $this->sendRequest($payload);
    }

    protected function eventLogger(): EventLogger
    {
        return app(EventLogger::class);
    }

    protected function jobLogger(): JobLogger
    {
        return app(JobLogger::class);
    }

    protected function queryLogger(): QueryLogger
    {
        return app(QueryLogger::class);
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
