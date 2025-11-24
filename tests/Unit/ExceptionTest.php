<?php

use Illuminate\Log\Events\MessageLogged;
use Illuminate\Support\Facades\Route;

it('will not send exceptions to ray if disabled', function () {
    ray()->stopShowingExceptions();

    $hasError = false;

    try {
        event(new MessageLogged('warning', 'test', ['exception' => new Exception('test')]));
    } catch (Exception $e) {
        $hasError = true;
    }

    expect($hasError)->toBeFalse();
});

it('includes request headers in exception meta', function () {
    ray()->showExceptions();

    Route::get('test-exception', function () {
        throw new Exception('Test exception');
    });

    try {
        $this->withHeaders([
            'X-Custom-Header' => 'test-value',
            'Accept' => 'application/json',
        ])->get('test-exception');
    } catch (Throwable $e) {
        // Expected to throw
    }

    $sentRequests = $this->client->sentRequests();

    $exceptionRequest = collect($sentRequests)
        ->first(fn ($request) => isset($request['payloads'][0]['type']) && $request['payloads'][0]['type'] === 'exception');

    expect($exceptionRequest)->not()->toBeNull();

    /** @var array $meta */
    $meta = $exceptionRequest['payloads'][0]['content']['meta'];

    expect($meta)->toHaveKey('request_headers');
    expect($meta['request_headers'])->toHaveKey('x-custom-header');
    expect($meta['request_headers']['x-custom-header'])->toBe('test-value');
    expect($meta['request_headers'])->toHaveKey('accept');
});

it('includes route context in exception meta', function () {
    ray()->showExceptions();

    Route::get('test-route', function () {
        throw new Exception('Test exception');
    })->name('test.route')->middleware('web');

    try {
        $this->get('test-route');
    } catch (Throwable $e) {
    }

    $sentRequests = $this->client->sentRequests();

    $exceptionRequest = collect($sentRequests)
        ->first(fn ($request) => isset($request['payloads'][0]['type']) && $request['payloads'][0]['type'] === 'exception');

    expect($exceptionRequest)->not()->toBeNull();

    $meta = $exceptionRequest['payloads'][0]['content']['meta'];

    expect($meta)->toHaveKey('application_route');
    expect($meta['application_route'])->toHaveKey('route name');
    expect($meta['application_route']['route name'])->toBe('test.route');
    expect($meta['application_route'])->toHaveKey('controller');
    expect($meta['application_route'])->toHaveKey('middleware');
    expect($meta['application_route']['middleware'])->toContain('web');
});

it('includes route parameters in exception meta', function () {
    ray()->showExceptions();

    Route::get('users/{id}/posts/{postId}', function ($id, $postId) {
        throw new Exception('Test exception');
    })->name('user.posts.show');

    try {
        $this->get('users/123/posts/456');
    } catch (Throwable $e) {
    }

    $sentRequests = $this->client->sentRequests();

    $exceptionRequest = collect($sentRequests)
        ->first(fn ($request) => isset($request['payloads'][0]['type']) && $request['payloads'][0]['type'] === 'exception');

    expect($exceptionRequest)->not()->toBeNull();

    $meta = $exceptionRequest['payloads'][0]['content']['meta'];

    expect($meta)->toHaveKey('application_route_parameters');
    expect($meta['application_route_parameters'])->toContain('123');
    expect($meta['application_route_parameters'])->toContain('456');
});

it('handles exceptions without active route', function () {
    ray()->showExceptions();

    event(new MessageLogged('error', 'test', ['exception' => new Exception('test')]));

    $sentRequests = $this->client->sentRequests();

    $exceptionRequest = collect($sentRequests)
        ->first(fn ($request) => isset($request['payloads'][0]['type']) && $request['payloads'][0]['type'] === 'exception');

    expect($exceptionRequest)->not()->toBeNull();

    $meta = $exceptionRequest['payloads'][0]['content']['meta'];

    // Should have the keys but they might be empty or null
    expect($meta)->toHaveKey('request_headers');
    expect($meta)->toHaveKey('application_route');
});
