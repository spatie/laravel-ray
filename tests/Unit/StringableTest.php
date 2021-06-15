<?php

namespace Spatie\LaravelRay\Tests\Unit;

use Illuminate\Support\Stringable;
use Spatie\LaravelRay\Tests\TestCase;

class StringableTest extends TestCase
{
    /** @test */
    public function it_has_a_chainable_stringable_macro_to_send_things_to_ray()
    {
        $str = new Stringable('Lorem');

        $str = $str->append(' Ipsum')->ray()->append(' Dolor Sit Amen');

        $this->assertInstanceOf(Stringable::class, $str);
        $this->assertSame('Lorem Ipsum Dolor Sit Amen', (string) $str);

        $this->assertCount(1, $this->client->sentPayloads());
    }
}
