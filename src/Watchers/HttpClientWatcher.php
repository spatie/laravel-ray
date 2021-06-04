<?php


namespace Spatie\LaravelRay\Watchers;


use Illuminate\Http\Client\Events\RequestSending;
use Illuminate\Http\Client\Events\ResponseReceived;
use Illuminate\Http\Client\Request;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Event;
use Spatie\Ray\Payloads\TablePayload;
use Spatie\LaravelRay\Ray;
use Spatie\Ray\Settings\Settings;
use SplObjectStorage;

class HttpClientWatcher extends Watcher
{
    /**
     * @var SplObjectStorage
     */
    protected $requestTimings;

    public function __construct()
    {
        $this->requestTimings = new SplObjectStorage();
    }

    public function register(): void
    {
        if (! static::supportedByLaravelVersion()) {
            return;
        }

        $settings = app(Settings::class);

        $this->enabled = $settings->send_http_client_requests_to_ray;

        Event::listen(RequestSending::class, function (RequestSending $event) {
            if (! $this->enabled()) {
                return;
            }

            $ray = $this->handleRequest($event->request);

            optional($this->rayProxy)->applyCalledMethods($ray);

            $this->requestTimings[$event->request] = microtime(true);
        });

        Event::listen(ResponseReceived::class, function (ResponseReceived $event) {
            if (! $this->enabled()) {
                return;
            }

            $ray = $this->handleResponse($event->request, $event->response);

            optional($this->rayProxy)->applyCalledMethods($ray);
        });
    }

    protected function handleRequest(Request $request)
    {
        $payload = new TablePayload([
            'Method' => $request->method(),
            'URL' => $request->url(),
            'Headers' => $request->headers(),
            'Data' => $request->data(),
            'Body' => $request->body(),
            'Type' => $this->getRequestType($request),
        ], 'Http');

        return app(Ray::class)->sendRequest($payload);
    }

    protected function getRequestType(Request $request)
    {
        if ($request->isJson()) {
            return 'Json';
        }

        if ($request->isMultipart()) {
            return 'Multipart';
        }

        return 'Form';
    }

    protected function handleResponse(Request $request, Response $response)
    {
        $payload = new TablePayload([
            'URL' => $request->url(),
            'Success' => $response->successful(),
            'Status' => $response->status(),
            'Headers' => $response->headers(),
            'Body' => rescue(function() use ($response) { return $response->json(); }, $response->body(), false),
            'Cookies' => $response->cookies(),
            'Duration' => $this->calculateResponseTime($request),
        ], 'Http');

        return app(Ray::class)->sendRequest($payload);
    }

    protected function calculateResponseTime(Request $request)
    {
        $timing = isset($this->requestTimings[$request])
            ? floor((microtime(true) - $this->requestTimings[$request]) * 1000)
            : null;

        unset($this->requestTimings[$request]);

        return $timing;
    }

    public static function supportedByLaravelVersion() {
        return version_compare(app()->version(), '8.45.0',  '>=');
    }
}
