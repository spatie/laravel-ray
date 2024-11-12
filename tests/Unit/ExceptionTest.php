<?php

use Illuminate\Log\Events\MessageLogged;

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
