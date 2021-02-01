<?php


namespace Spatie\LaravelRay\Tests\Unit;


use Illuminate\Support\Facades\Route;
use Spatie\LaravelRay\Tests\TestCase;

class RequestTest extends TestCase
{
    /** @test */
    public function it_can_listen_to_requests()
    {
        Route::get('test', function () {
            return 'ok';
        });

        ray()->requests();

        $this->get('test');

        $this->assertMatchesOsSafeSnapshot($this->client->sentPayloads());
    }
}
