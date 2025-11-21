<?php

namespace Spatie\LaravelRay\Watchers;

use Closure;
use Exception;
use Facade\FlareClient\Flare as FacadeFlare;
use Facade\FlareClient\Truncation\ReportTrimmer as FacadeReportTrimmer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Log\Events\MessageLogged;
use Illuminate\Support\Facades\Event;
use Spatie\FlareClient\Flare;
use Spatie\FlareClient\Truncation\ReportTrimmer;
use Spatie\LaravelRay\Ray;
use Spatie\Ray\Settings\Settings;
use Throwable;

class ExceptionWatcher extends Watcher
{
    public function register(): void
    {
        $settings = app(Settings::class);

        $this->enabled = $settings->send_exceptions_to_ray;

        Event::listen(MessageLogged::class, function (MessageLogged $message) {
            if (! $this->enabled()) {
                return;
            }

            if (! $this->concernsException($message)) {
                return;
            }

            $exception = $message->context['exception'];

            $meta = [];

            if ($flareReport = $this->getFlareReport($exception)) {
                $meta['flare_report'] = $flareReport;
            }

            $exceptionContext = $this->getRequestAndRouteContext();

            $meta = array_merge($meta, $exceptionContext);

            /** @var Ray $ray */
            $ray = app(Ray::class);

            $ray->exception(
                $exception,
                $meta,
            );
        });
    }

    public function concernsException(MessageLogged $messageLogged): bool
    {
        if (! isset($messageLogged->context['exception'])) {
            return false;
        }

        if (! $messageLogged->context['exception'] instanceof Exception) {
            return false;
        }

        return true;
    }

    public function getFlareReport(Throwable $exception): ?array
    {
        if (app()->bound(Flare::class)) {
            $flare = app(Flare::class);

            $report = $flare->createReport($exception);

            return (new ReportTrimmer)->trim($report->toArray());
        }

        if (app()->bound(FacadeFlare::class)) {
            /** @var \Facade\FlareClient\Flare $flare */
            $flare = app(FacadeFlare::class);

            $report = $flare->createReport($exception);

            return (new FacadeReportTrimmer)->trim($report->toArray());
        }

        return null;
    }

    protected function getRequestAndRouteContext(): array
    {
        return [
            'request_headers' => $this->getRequestHeaders(),
            'application_route' => $this->getApplicationRouteContext(),
            'application_route_parameters' => $this->getApplicationRouteParameters(),
        ];
    }

    /**
     * Get the request's headers.
     *
     * @return array<string, string>
     */
    protected function getRequestHeaders(): array
    {
        return array_map(function (array $header) {
            return implode(', ', $header);
        }, request()->headers->all());
    }

    /**
     * Get the application's route context.
     *
     * @return array<string, string>
     */
    protected function getApplicationRouteContext(): array
    {
        $route = request()->route();

        return $route ? array_filter([
            'controller' => $route->getActionName(),
            'route name' => $route->getName() ?: null,
            'middleware' => implode(', ', array_map(function ($middleware) {
                return $middleware instanceof Closure ? 'Closure' : $middleware;
            }, $route->gatherMiddleware())),
        ]) : [];
    }

    /**
     * Get the application's route parameters context.
     *
     * @return array<string, mixed>|null
     */
    protected function getApplicationRouteParameters(): array
    {
        $route = request()->route();

        $parameters = $route ? $route->parameters() : null;

        return $parameters ? json_encode(array_map(
            fn ($value) => $value instanceof Model ? $value->withoutRelations() : $value,
            $parameters
        ), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) : null;
    }
}
