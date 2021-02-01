<?php

namespace Spatie\LaravelRay\Tests\Unit;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Route;
use Spatie\LaravelRay\Tests\Concerns\MatchesOsSafeSnapshots;
use Spatie\LaravelRay\Tests\TestCase;

class JsonTest extends TestCase
{
    /** @test */
    public function it_can_send_a_json_test_response_to_ray()
    {
        Route::get('test', function () {
            return response()->json(['a' => 1]);
        });

        $this
            ->get('test')
            ->ray()
            ->assertSuccessful();

        $this->assertCount(1, $this->client->sentPayloads());

        $this->assertEquals(200, Arr::get($this->client->sentPayloads(), '0.payloads.0.content.status_code'));
        $this->assertStringContainsString('application/json', Arr::get($this->client->sentPayloads(), '0.payloads.0.content.headers'));

        $this->assertEquals('{"a":1}', Arr::get($this->client->sentPayloads(), '0.payloads.0.content.content'));
        $this->assertEquals('{"a":1}', Arr::get($this->client->sentPayloads(), '0.payloads.0.content.content'));
        $this->assertNotEmpty(Arr::get($this->client->sentPayloads(), '0.payloads.0.content.json'));
    }

    /** @test */
    public function it_can_send_a_regular_test_response_to_ray()
    {
        Route::get('test', function () {
            return response('hello', 201);
        });

        $this
            ->get('test')
            ->ray();

        $this->assertCount(1, $this->client->sentPayloads());
        $this->assertEquals(201, Arr::get($this->client->sentPayloads(), '0.payloads.0.content.status_code'));

        $this->assertStringContainsString('text/html; charset=UTF-8', Arr::get($this->client->sentPayloads(), '0.payloads.0.content.headers'));

        $this->assertEquals('hello', Arr::get($this->client->sentPayloads(), '0.payloads.0.content.content'));

        $this->assertEmpty(Arr::get($this->client->sentPayloads(), '0.payloads.0.content.json'));
    }
}
