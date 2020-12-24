<?php

namespace Spatie\LaravelRay\Tests\TestClasses;

class TestEventWithParameter
{
    protected string $parameter;

    public function __construct(string $parameter)
    {
        $this->parameter = $parameter;
    }
}
