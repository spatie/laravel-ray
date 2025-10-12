<?php

use Spatie\LaravelRay\OriginFactory;

it('returns correct origin for non-Invador callers', function () {
    $expectedLineNumber = __LINE__ + 1;
    $origin = new OriginFactory()->getOrigin();

    expect($origin->file)->toEqual(__FILE__);
    expect($origin->lineNumber)->toEqual($expectedLineNumber);
});
