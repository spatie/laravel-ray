<?php

namespace Spatie\LaravelRay\Tests;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Orchestra\Testbench\TestCase as Orchestra;
use Spatie\LaravelRay\Ray;
use Spatie\LaravelRay\RayServiceProvider;
use Spatie\LaravelRay\Tests\TestClasses\FakeClient;
use Spatie\Ray\Origin\Hostname;
use Spatie\Ray\Settings\Settings;

class TestCase extends Orchestra
{
    /** @var \Spatie\LaravelRay\Tests\TestClasses\FakeClient */
    protected $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = new FakeClient();

        $this->app->bind(Ray::class, function () {
            $settings = app(Settings::class);

            $ray = new Ray($settings, $this->client, 'fakeUuid');

            if (! $settings->enable) {
                $ray->disable();
            }

            return $ray;
        });

        Hostname::set('fake-hostname');

        View::addLocation(__DIR__ . '/resources/views');
    }

    protected function getPackageProviders($app)
    {
        return [
            RayServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('email')->nullable();
        });
    }

    protected function useRealUuid()
    {
        $this->app->bind(Ray::class, function () {
            Ray::$fakeUuid = null;

            return Ray::create($this->client);
        });
    }

    protected function assertSqlContains($queryContent, $needle): void
    {
        $sql = method_exists(Builder::class, 'toRawSql')
            ? $queryContent['sql']
            : Str::replaceArray('?', $queryContent['bindings'], $queryContent['sql']);

        $this->assertStringContainsString($needle, $sql);
    }
}
