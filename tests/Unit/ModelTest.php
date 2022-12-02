<?php

use Spatie\LaravelRay\Tests\TestClasses\User;

it('can send one model to ray', function () {
    $user = User::make(['email' => 'john@example.com']);

    ray()->model($user);

    expect($this->client->sentRequests())->toHaveCount(1);
});

it('can send multiple models to ray', function () {
    $user1 = User::make(['email' => 'john@example.com']);
    $user2 = User::make(['email' => 'paul@example.com']);

    ray()->model($user1, $user2);
    expect($this->client->sentRequests())->toHaveCount(2);
});

it('can send a single models to ray using models', function () {
    $user = User::make(['email' => 'john@example.com']);

    ray()->models($user);

    expect($this->client->sentRequests())->toHaveCount(1);
});

it('can send a collection of models to ray using models', function () {
    $user1 = User::make(['email' => 'john@example.com']);
    $user2 = User::make(['email' => 'paul@example.com']);

    ray()->models(collect([$user1, $user2]));

    expect($this->client->sentRequests())->toHaveCount(2);
});
