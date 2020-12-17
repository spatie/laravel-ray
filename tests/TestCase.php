<?php

namespace Spatie\LaravelRay\Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Orchestra\Testbench\TestCase as Orchestra;
use Spatie\LaravelRay\Ray;
use Spatie\LaravelRay\RayServiceProvider;
use Spatie\LaravelRay\Tests\TestClasses\FakeClient;

class TestCase extends Orchestra
{
    protected FakeClient $client;

    public function setUp(): void
    {
        parent::setUp();

        $this->client = new FakeClient();

        $this->app->bind(Ray::class, function () {
            return (new Ray($this->client, 'fakeUuid'));
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
