<?php


class ArrayFilterNullTest extends PHPUnit_Framework_TestCase
{
    public function test()
    {
        $this->assertSame([
            0 => 1,
            1 => false,
            3 => 0,
            4 => 'a',
        ], Meare\Juggler\array_filter_null([
            0 => 1,
            1 => false,
            2 => null,
            3 => 0,
            4 => 'a',
        ]));
    }
}
