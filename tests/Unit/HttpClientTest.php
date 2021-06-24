<?php


namespace Spatie\LaravelRay\Tests\Unit;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Spatie\LaravelRay\Tests\TestCase;
use Spatie\LaravelRay\Watchers\HttpClientWatcher;

class HttpClientTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        if (! HttpClientWatcher::supportedByLaravelVersion()) {
            $this->markTestSkipped('Tests require Laravel 8.45.0 or greater.');
        }

        Http::fake(
            [
                '*/ok*' => Http::response(['hello' => 'world'], 200, ['Content-Type' => 'application/json']),
                '*/not-found*' => Http::response(null, 404),
                '*/json*' => Http::response(['foo' => 'bar']),
            ]
        );
    }

    /** @test */
    public function it_can_listen_to_http_client_requests()
    {
        ray()->showHttpClientRequests();

        Http::get('test.com/ok', ['hello' => 'world']);

        $this->assertEquals('test.com/ok?hello=world', Arr::get($this->client->sentRequests(), '0.payloads.0.content.values')['URL']);
        $this->assertEquals('Http', Arr::get($this->client->sentRequests(), '0.payloads.0.content.label'));
    }

    /** @test */
    public function it_can_listen_to_http_client_responses()
    {
        ray()->showHttpClientRequests();

        Http::get('test.com/json');

        $this->assertEquals('test.com/json', Arr::get($this->client->sentRequests(), '1.payloads.0.content.values')['URL']);
        $this->assertEquals('Http', Arr::get($this->client->sentRequests(), '1.payloads.0.content.label'));
    }

    /** @test */
    public function it_can_listen_for_non_successful_requests()
    {
        ray()->showHttpClientRequests();

        Http::get('test.com/not-found');

        $this->assertEquals('404', Arr::get($this->client->sentRequests(), '1.payloads.0.content.values')['Status']);
    }

    /** @test */
    public function it_doesnt_send_a_payload_when_disabled()
    {
        Http::get('test.com/not-found');

        $this->assertEmpty($this->client->sentRequests());
    }

    /** @test */
    public function show_http_client_can_be_colorized()
    {
        $this->useRealUuid();

        ray()->showHttpClientRequests()->green();

        Http::get('test.com/ok');

        $sentPayloads = $this->client->sentRequests();

        $this->assertCount(4, $sentPayloads); // 2 for the request and 2 for the response.
        $this->assertEquals($sentPayloads[0]['uuid'], $sentPayloads[1]['uuid']);
        $this->assertNotEquals('fakeUuid', $sentPayloads[0]['uuid']);
    }
}
