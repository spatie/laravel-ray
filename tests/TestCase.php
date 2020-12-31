<?php

namespace Spatie\LaravelRay\Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Orchestra\Testbench\TestCase as Orchestra;
use Spatie\LaravelRay\Ray;
use Spatie\LaravelRay\RayServiceProvider;
use Spatie\LaravelRay\Tests\TestClasses\FakeClient;
use Spatie\Ray\Settings\Settings;

class TestCase extends Orchestra
{
    protected FakeClient $client;

    public function setUp(): void
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

        View::addLocation(__DIR__ . '/resources/view');
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
            $table->bigInteger('id');
        });
    }
}
