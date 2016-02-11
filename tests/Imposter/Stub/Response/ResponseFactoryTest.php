<?php

namespace Meare\Juggler\Test\Imposter\Stub\Response;


use Meare\Juggler\Imposter\Stub\Response\IResponse;
use Meare\Juggler\Imposter\Stub\Response\ResponseFactory;
use PHPUnit_Framework_TestCase;

class ResponseFactoryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \Meare\Juggler\Imposter\Stub\Response\ResponseFactory
     */
    private $factory;

    public function setUp()
    {
        $this->factory = new ResponseFactory;
    }

    public function testCreateIsResponse()
    {
        $this->assertInstanceOf(
            \Meare\Juggler\Imposter\Stub\Response\IsResponse::class,
            $this->factory->createInstance(IResponse::TYPE_IS, [
                'statusCode' => 201,
            ])
        );
    }

    public function testCreateProxy()
    {
        $this->assertInstanceOf(
            \Meare\Juggler\Imposter\Stub\Response\ProxyResponse::class,
            $this->factory->createInstance(IResponse::TYPE_PROXY, [
                'to' => 'http://google.com',
            ])
        );
    }

    public function testCreateInjection()
    {
        $this->assertInstanceOf(
            \Meare\Juggler\Imposter\Stub\Injection::class,
            $this->factory->createInstance(IResponse::TYPE_INJECT, 'function(){}')
        );
    }

    public function testInvalidType()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->factory->createInstance('invalid type', 'does not really matter');
    }
}
