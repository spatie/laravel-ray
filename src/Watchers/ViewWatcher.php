<?php

namespace Spatie\LaravelRay\Watchers;

use Illuminate\Support\Facades\Event;
use Spatie\LaravelRay\Ray;

class ViewWatcher extends Watcher
{
    public function register(): void
    {
        Event::listen('composing:*', function ($event, $data) {
            if (!$this->enabled()) {
                return;
            }

            /** @var \Illuminate\View\View $view */
            $view = $data[0];

            app(Ray::class)->view($view);
        });
    }
}
