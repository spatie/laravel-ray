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

it('can show only type queries', function (Closure $rayShowMethod, Closure $rayStopMethod, string $sqlCommand) {
    $rayShowMethod();

    $user = User::query()->firstOrCreate(['email' => 'john@example.com']);
    $user->update(['email' => 'joan@example.com']);
    $user->delete();

    expect($this->client->sentPayloads())->toHaveCount(1);

    $payload = $this->client->sentPayloads();

    $this->assertStringStartsWith($sqlCommand, Arr::get($payload, '0.content.sql'));

    $rayStopMethod();

    $user = User::query()->firstOrCreate(['email' => 'sam@example.com']);
    $user->update(['email' => 'sarah@example.com']);
    $user->delete();

    expect($this->client->sentPayloads())->toHaveCount(1);
})->with([
    'update' => [function () {ray()->showUpdateQueries();}, function () {ray()->stopShowingUpdateQueries();}, 'update'],
    'delete' => [function () {ray()->showDeleteQueries();}, function () {ray()->stopShowingDeleteQueries();}, 'delete'],
    'insert' => [function () {ray()->showInsertQueries();}, function () {ray()->stopShowingInsertQueries();}, 'insert'],
    'select' => [function () {ray()->showSelectQueries();}, function () {ray()->stopShowingSelectQueries();}, 'select'],
]);
