<?php

namespace Spatie\LaravelTimber\Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as Orchestra;
use Spatie\LaravelTimber\Tests\TestClasses\FakeClient;
use Spatie\LaravelTimber\Timber;
use Spatie\LaravelTimber\TimberServiceProvider;

class TestCase extends Orchestra
{
    protected FakeClient $client;

    public function setUp(): void
    {
        parent::setUp();

        $this->client = new FakeClient();

        $this->app->bind(Timber::class, function () {
            return (new Timber($this->client, 'fakeUuid'));
        });
    }

    protected function getPackageProviders($app)
    {
        return [
            TimberServiceProvider::class,
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
