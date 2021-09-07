<?php

namespace Spatie\LaravelRay\Tests\Concerns;

use Spatie\Snapshots\MatchesSnapshots;

trait MatchesOsSafeSnapshots
{
    use MatchesSnapshots;

    protected function assertMatchesOsSafeSnapshot($data)
    {
        // fix paths when running unit tests on windows platform (github actions)
        $json = json_encode($data);
        $json = str_replace('D:\\\\a\\\\laravel-ray\\\\laravel-ray', '', $json);
        $json = str_replace('\\\\', '/', $json);


        $this->assertMatchesJsonSnapshot($json);
    }
}
