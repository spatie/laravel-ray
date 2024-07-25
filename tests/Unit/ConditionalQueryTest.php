<?php

use Illuminate\Support\Arr;
use Spatie\LaravelRay\Tests\TestClasses\User;

it('can show only update queries and return the results', function () {
    $user = ray()->showUpdateQueries(function (): User {
        $user = User::query()->create(['email' => 'john@example.com']);
        $user->update(['email' => 'joan@example.com']);

        return $user;
    });

    expect($this->client->sentRequests())->toHaveCount(1);
    expect($user)->toBeInstanceOf(User::class);

    $this->assertSame('joan@example.com', $user->email);
});

it('can stop showing update queries', function () {
    $user = User::query()->create(['email' => 'john@example.com']);

    ray()->showUpdateQueries();
    $user->update(['email' => 'joan@example.com']);
    ray()->stopShowingUpdateQueries();
    $user->update(['email' => 'joe@example.com']);

    expect($this->client->sentRequests())->toHaveCount(1);
});

it('can show only type queries', function (Closure $rayShowMethod, string $sqlCommand) {
    $rayShowMethod();

    $user = User::query()->create(['email' => 'john@example.com']);
    $user->update(['email' => 'joan@example.com']);
    $user = User::query()->find($user->id);
    $user->delete();

    expect($this->client->sentPayloads())->toHaveCount(1);

    $payload = $this->client->sentPayloads();

    $this->assertStringStartsWith($sqlCommand, Arr::get($payload, '0.content.sql'));
})->with([
    'update' => [function () {ray()->showUpdateQueries();}, 'update'],
    'delete' => [function () {ray()->showDeleteQueries();}, 'delete'],
    'insert' => [function () {ray()->showInsertQueries();}, 'insert'],
    'select' => [function () {ray()->showSelectQueries();}, 'select'],
]);
