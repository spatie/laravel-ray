<?php

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Spatie\LaravelRay\Tests\TestClasses\User;

it('can start logging queries', function () {
    ray()->showQueries();

    DB::table('users')->get('id');

    expect($this->client->sentRequests())->toHaveCount(1);
});

it('can start logging queries using alias', function () {
    ray()->queries();

    DB::table('users')->get('id');

    expect($this->client->sentRequests())->toHaveCount(1);
});

it('can stop logging queries', function () {
    ray()->showQueries();

    DB::table('users')->get('id');
    DB::table('users')->get('id');
    expect($this->client->sentRequests())->toHaveCount(2);

    ray()->stopShowingQueries();
    DB::table('users')->get('id');
    expect($this->client->sentRequests())->toHaveCount(2);
});

it('calling log queries twice will not log all queries twice', function () {
    ray()->showQueries();
    ray()->showQueries();

    DB::table('users')->get('id');

    expect($this->client->sentRequests())->toHaveCount(1);
});

it('can log all queries in a callable', function () {
    ray()->showQueries(function () {
        // will be logged
        DB::table('users')->where('id', 1)->get();
    });
    expect($this->client->sentRequests())->toHaveCount(1);

    // will not be logged
    DB::table('users')->get('id');
    expect($this->client->sentRequests())->toHaveCount(1);
});

it('can log all queries in a callable and gets results', function () {
    $results = ray()->showQueries(function (): \Illuminate\Support\Collection {
        // will be logged
        return DB::table('users')->where('id', 1)->get();
    });
    expect($this->client->sentRequests())->toHaveCount(1);
    expect($results)->toBeInstanceOf(\Illuminate\Support\Collection::class);
    expect($results->count())->toEqual(0);
});

it('show queries can be colorized', function () {
    $this->useRealUuid();

    ray()->showQueries()->green();

    DB::table('users')->where('id', 1)->get();

    $sentPayloads = $this->client->sentRequests();

    expect($sentPayloads)->toHaveCount(2);
    expect($sentPayloads[1]['uuid'])->toEqual($sentPayloads[0]['uuid']);
    expect($sentPayloads[0]['uuid'])->not->toEqual('fakeUuid');
});

it('can count the amount of executed queries', function () {
    ray()->countQueries(function () {
        DB::table('users')->get('id');
        DB::table('users')->get('id');
        DB::table('users')->get('id');
    });

    expect($this->client->sentRequests())->toHaveCount(1);

    $payload = $this->client->sentRequests()[0];

    expect(Arr::get($payload, 'payloads.0.content.values.Count'))->toEqual(3);
});

it('an eloquent query can be sent to ray', function () {
    User::create(['email' => 'john@example.com']);

    $user = User::query()->where('email', 'john@example.com')->ray()->first();

    expect($this->client->sentPayloads())->toHaveCount(1);

    $payload = $this->client->sentPayloads()[0];

    expect(Arr::get($payload, 'type'))->toEqual('executed_query');

    expect($user)->toBeInstanceOf(User::class);
});
