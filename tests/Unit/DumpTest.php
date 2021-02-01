<?php

namespace Spatie\LaravelRay\Tests\Unit;

use Illuminate\Support\Facades\DB;
use Spatie\LaravelRay\Tests\Concerns\MatchesOsSafeSnapshots;
use Spatie\LaravelRay\Tests\TestCase;

class DumpTest extends TestCase
{
    use MatchesOsSafeSnapshots;

    /** @test */
    public function it_can_log_dumps()
    {
        dump('test');

        $this->assertCount(1, $this->client->sentPayloads());
    }
}
