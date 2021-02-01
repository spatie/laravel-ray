<?php

namespace Spatie\LaravelRay\Tests\Unit;

use Spatie\LaravelRay\Tests\Concerns\MatchesOsSafeSnapshots;
use Spatie\LaravelRay\Tests\TestCase;
use Spatie\LaravelRay\Tests\TestClasses\User;

class ModelTest extends TestCase
{
    use MatchesOsSafeSnapshots;

    /** @test */
    public function it_can_send_one_model_to_ray()
    {
        $user = User::make(['email' => 'john@example.com']);

        ray()->model($user);

        $this->assertCount(1, $this->client->sentPayloads());
    }

    /** @test */
    public function it_can_send_multiple_models_to_ray()
    {
        $user1 = User::make(['email' => 'john@example.com']);
        $user2 = User::make(['email' => 'paul@example.com']);

        ray()->model($user1, $user2);
        $this->assertCount(2, $this->client->sentPayloads());
    }

    /** @test */
    public function it_can_send_a_single_models_to_ray_using_models()
    {
        $user = User::make(['email' => 'john@example.com']);

        ray()->models($user);

        $this->assertCount(1, $this->client->sentPayloads());
    }

    /** @test */
    public function it_can_send_a_collection_of_models_to_ray_using_models()
    {
        $user1 = User::make(['email' => 'john@example.com']);
        $user2 = User::make(['email' => 'paul@example.com']);

        ray()->models(collect([$user1, $user2]));

        $this->assertCount(2, $this->client->sentPayloads());
    }
}
