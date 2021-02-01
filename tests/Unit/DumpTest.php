<?php

namespace Spatie\LaravelRay\Tests\Unit;

use Spatie\LaravelRay\Tests\Concerns\MatchesOsSafeSnapshots;
use Spatie\LaravelRay\Tests\TestCase;

class DumpTest extends TestCase
{
    /** @test */
    public function it_can_log_dumps()
    {
        dump('test');

        $this->assertCount(1, $this->client->sentPayloads());
    }
}
