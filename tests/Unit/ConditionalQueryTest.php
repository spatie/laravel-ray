<?php

use Illuminate\Support\Arr;
use Spatie\LaravelRay\Tests\TestClasses\User;
use Spatie\LaravelRay\Watchers\SelectQueryWatcher;
use Spatie\Ray\Settings\Settings;

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

    // Run all query types
    $user = User::query()->firstOrCreate(['email' => 'john@example.com']);
    $user->update(['email' => 'joan@example.com']);
    $user->delete();

    expect($this->client->sentPayloads())->toHaveCount(1);

    // Assert the one we want is picked up.
    $payload = $this->client->sentPayloads();
    $this->assertStringStartsWith($sqlCommand, Arr::get($payload, '0.content.sql'));

    $rayStopMethod();

    // Assert that watcher has stopped.
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

it('can take a custom condition and only return those queries', function () {
    ray()->showConditionalQueries(function (string $query) {
        return str_contains($query, 'joan');
    });

    User::query()->create(['email' => 'joan@example.com']);
    User::query()->create(['email' => 'john@example.com']);

    expect($this->client->sentPayloads())->toHaveCount(1);

    $payload = $this->client->sentPayloads();
    $this->assertStringContainsString('joan@example.com', Arr::get($payload, '0.content.sql'));

    ray()->stopShowingConditionalQueries();

    User::query()->create(['email' => 'joanne@example.com']);

    expect($this->client->sentPayloads())->toHaveCount(1);
});

it('can handle multiple conditional query watchers', function () {
    $john = ray()->showConditionalQueries(
        function (string $query) {
            return str_contains($query, 'joan');
        },
        function (): User {
            ray()->showConditionalQueries(
                function (string $query) {
                    return str_contains($query, 'john');
                },
                null,
                'look for john'
            );

            User::query()->create(['email' => 'joan@example.com']);
            User::query()->create(['email' => 'joe@example.com']);

            return User::query()->create(['email' => 'john@example.com']);
        },
        'look for joan'
    );

    // Assert that john was handed back
    $this->assertSame('john@example.com', $john->email);

    // Assert that ray only received what we wanted
    expect($this->client->sentPayloads())->toHaveCount(2);

    $payload = $this->client->sentPayloads();

    // Assert that ray received the correct order
    $this->assertStringContainsString('joan@example.com', Arr::get($payload, '0.content.sql'));
    $this->assertStringContainsString('john@example.com', Arr::get($payload, '1.content.sql'));

    // Looking for joan has been disabled so this should not be sent
    $joan = User::query()->where('email', 'joan@example.com')->sole();
    expect($this->client->sentPayloads())->toHaveCount(2);

    // Looking for john is still enabled so this should be sent
    $john->update(['email' => 'john@adifferentdomain.com']);
    expect($this->client->sentPayloads())->toHaveCount(3);

    ray()->stopShowingConditionalQueries('look for john');

    // Looking for john has been disabled so this should not be sent
    $joan->update(['email' => 'iamjohnnow@example.com']);
    expect($this->client->sentPayloads())->toHaveCount(3);
});

it('can start watching from config only', function () {
    app(Settings::class)->send_select_queries_to_ray = true;

    // Refresh the watcher and register again to pick up settings change
    $this->app->singleton(SelectQueryWatcher::class);
    app(SelectQueryWatcher::class)->register();

    // Run all query types
    $user = User::query()->firstOrCreate(['email' => 'john@example.com']);
    $user->update(['email' => 'joan@example.com']);
    $user->delete();

    expect($this->client->sentPayloads())->toHaveCount(1);

    $payload = $this->client->sentPayloads();
    $this->assertStringStartsWith('select', Arr::get($payload, '0.content.sql'));
});
