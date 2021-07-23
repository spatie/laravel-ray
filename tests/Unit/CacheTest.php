<?php

namespace Spatie\LaravelRay\Tests\Unit;

use Illuminate\Support\Facades\Cache;
use Spatie\LaravelRay\Tests\TestCase;

class CacheTest extends TestCase
{
    /** @test */
    public function it_can_detect_when_something_gets_cached()
    {
        ray()->showCache();

        Cache::put('cached-key', 'cached-value');

        $this->assertMatchesOsSafeSnapshot($this->client->sentRequests());
    }

    /** @test */
    public function it_will_not_report_caches_by_default()
    {
        Cache::put('cached-key', 'cached-value');

        $this->assertCount(0, $this->client->sentRequests());
    }

    /** @test */
    public function the_cache_watcher_can_be_disabled()
    {
        ray()->showCache();

        Cache::put('cached-key', 'cached-value');

        ray()->stopShowingCache();

        Cache::put('another-key', 'another-value');

        $this->assertCount(1, $this->client->sentRequests());
    }

    /** @test */
    public function it_can_detect_when_the_cache_is_hit()
    {
        ray()->showCache();

        Cache::put('cached-key', 'cached-value');

        Cache::get('cached-key');

        $this->assertMatchesOsSafeSnapshot($this->client->sentRequests());
    }

    /** @test */
    public function it_can_detect_when_the_cache_is_missed()
    {
        ray()->showCache();

        Cache::get('cached-key');

        $this->assertMatchesOsSafeSnapshot($this->client->sentRequests());
    }

    /** @test */
    public function it_can_detect_when_something_gets_temporarily_cached()
    {
        ray()->showCache();

        Cache::put('cached-key', 'cached-value', 10);

        $this->assertMatchesOsSafeSnapshot($this->client->sentRequests());
    }

    /** @test */
    public function it_can_detect_when_something_is_cleared_from_the_cache()
    {
        ray()->showCache();

        Cache::put('cached-key', 'cached-value', 10);

        Cache::pull('cached-key');

        $this->assertMatchesOsSafeSnapshot($this->client->sentRequests());
    }
}
