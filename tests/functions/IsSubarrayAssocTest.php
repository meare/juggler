<?php

class IsSubarrayAssocTest extends PHPUnit_Framework_TestCase
{
    public function testNonEmptyArrayIsNotSubarrayOfEmptyArray()
    {
        $this->assertFalse(\Meare\Juggler\is_subarray_assoc(
            [1],
            []
        ));
    }

    public function testEmptyArrayIsSubarrayOfEmptyArray()
    {
        $this->assertTrue(\Meare\Juggler\is_subarray_assoc([], []));
    }

    public function testOneDimensionalArrayIsSubarray()
    {
        $this->assertTrue(\Meare\Juggler\is_subarray_assoc(
            ['b' => 2, 'c' => 3],
            ['a' => 1, 'b' => 2, 'c' => 3]
        ));
    }

    public function testOneDimensionalArrayIsNotSubarrayBecauseOfValue()
    {
        $this->assertFalse(\Meare\Juggler\is_subarray_assoc(
            ['c' => 4],
            ['a' => 1, 'b' => 2, 'c' => 3]
        ));
    }

    public function testOneDimensionalArrayIsNotSubarrayBecauseOfKey()
    {
        $this->assertFalse(\Meare\Juggler\is_subarray_assoc(
            ['d' => 3],
            ['a' => 1, 'b' => 2, 'c' => 3]
        ));
    }

    public function testMultiDimensionalArrayIsSubarray()
    {
        $this->assertTrue(\Meare\Juggler\is_subarray_assoc(
            ['b' => ['bb' => 22, 'cc' => 33]],
            ['a' => 1, 'b' => ['aa' => 11, 'bb' => 22, 'cc' => 33], 'c' => 3]
        ));
    }

    public function testMultiDimensionalArrayIsNotSubarray()
    {
        $this->assertFalse(\Meare\Juggler\is_subarray_assoc(
            ['b' => ['bb' => 11, 'cc' => 33]],
            ['a' => 1, 'b' => ['aa' => 11, 'bb' => 22, 'cc' => 33], 'c' => 3]
        ));
    }
}
