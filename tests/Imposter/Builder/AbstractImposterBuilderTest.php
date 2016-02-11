<?php

namespace Meare\Juggler\Test\Imposter\Builder;


use Meare\Juggler\Imposter\Builder\AbstractImposterBuilder;
use PHPUnit_Framework_TestCase;

class AbstractImposterBuilderTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \Meare\Juggler\Imposter\Builder\AbstractImposterBuilder
     */
    private $abstractImposterBuilder;

    public function setUp()
    {
        $this->abstractImposterBuilder = new AbstractImposterBuilder;
    }

    public function testInvalidJson()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->abstractImposterBuilder->build('invalid_json');
    }

    public function testProtocolUnset()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->abstractImposterBuilder->build('{"port":2525}');
    }

    public function testBuilderDoesNotExist()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->abstractImposterBuilder->build('{"protocol":"tcp"}');
    }

//    public function testHttpBuilder()
//    {
//        $this->assertInstanceOf(
//            HttpImposterBuilder::class,
//            $this->abstractImposterBuilder->build('{"protocol":"http"}')
//        );
//    }
}
