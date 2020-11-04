<?php

namespace Spatie\LaravelTimber\Tests;

use Spatie\Snapshots\MatchesSnapshots;

class TimberTest extends TestCase
{
    use MatchesSnapshots;

    /** @test */
    public function it_can_send_a_something_to_timber()
    {
        timber('a');

        $this->assertMatchesSnapshot($this->client->sentPayloads());
    }
}
