<?php


namespace Spatie\LaravelRay\Tests\Unit;

use Illuminate\Support\Arr;
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

        $this->assertEquals(200, Arr::get($this->client->sentRequests(), '0.payloads.0.content.values')['Response code']);
    }

    /** @test */
    public function it_can_listen_to_requests_that_return_json()
    {
        Route::get('test-json', function () {
            return response()->json(['message' => 'ok']);
        });

        ray()->requests();

        $this->get('test-json');

        $this->assertEquals(200, Arr::get($this->client->sentRequests(), '0.payloads.0.content.values')['Response code']);
    }

    /** @test */
    public function it_can_listen_to_requests_that_return_text()
    {
        Route::get('test-text', function () {
            return response('ok', 200, ['content-type' => 'text/plain']);
        });

        ray()->requests();

        $this->get('test-text');

        $this->assertEquals(200, Arr::get($this->client->sentRequests(), '0.payloads.0.content.values')['Response code']);
    }

    /** @test */
    public function it_can_listen_to_requests_that_return_redirects()
    {
        Route::get('test-redirect', function () {
            return response()->redirectTo('/');
        });

        ray()->requests();

        $this->get('test-redirect');

        $this->assertEquals(302, Arr::get($this->client->sentRequests(), '0.payloads.0.content.values')['Response code']);
    }

    /** @test */
    public function show_request_can_be_colorized()
    {
        $this->useRealUuid();

        ray()->showRequests()->green();

        $this->get('test-redirect');

        $sentPayloads = $this->client->sentRequests();

        $this->assertCount(2, $sentPayloads);
        $this->assertEquals($sentPayloads[0]['uuid'], $sentPayloads[1]['uuid']);
        $this->assertNotEquals('fakeUuid', $sentPayloads[0]['uuid']);
    }
}
