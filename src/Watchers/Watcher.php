<?php

namespace Spatie\LaravelRay\Watchers;

abstract class Watcher
{
    /** @var bool */
    protected $enabled = false;

    abstract public function register(): void;

    public function enabled(): bool
    {
        return $this->enabled;
    }

    public function enable(): Watcher
    {
        $this->enabled = true;

        return $this;
    }

    public function disable(): Watcher
    {
        $this->enabled = false;

        return $this;
    }
}
