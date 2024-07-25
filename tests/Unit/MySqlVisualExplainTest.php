<?php

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Spatie\LaravelRay\Tests\TestClasses\User;
use Tpetry\MysqlExplain\Facades\MysqlExplain;
use Tpetry\MysqlExplain\Mixins\BuilderMixin;

it('can be used with the query builder', function ($builder) {
    EloquentBuilder::mixin(new BuilderMixin());
    QueryBuilder::mixin(new BuilderMixin());

    MysqlExplain::shouldReceive('submitBuilder')
        ->once()
        ->andReturn('https://dummy-url-f6V7VImZnz.local');

    $builder->rayVisualExplain();

    expect($this->client->sentPayloads())->toHaveCount(1);

    $payload = $this->client->sentPayloads()[0];

    expect(Arr::get($payload, 'type'))->toEqual('mysql_visual_explain');
    expect($payload['content']['url'])->toEqual('https://dummy-url-f6V7VImZnz.local');
})->with([
    fn () => User::where('email', 'john@example.com'),
    fn () => DB::table('users')->where('email', 'john@example.com'),
]);

it('should work with raw queries', function () {
    MysqlExplain::shouldReceive('submitQuery')
        ->once()
        ->andReturn('https://dummy-url-f6V7VImZnz.local');

    ray()->visualExplain('SELECT * FROM users WHERE email = ?', ['freek@spatie.be']);

    expect($this->client->sentPayloads())->toHaveCount(1);

    $payload = $this->client->sentPayloads()[0];

    expect(Arr::get($payload, 'type'))->toEqual('mysql_visual_explain');
    expect($payload['content']['url'])->toEqual('https://dummy-url-f6V7VImZnz.local');
});
