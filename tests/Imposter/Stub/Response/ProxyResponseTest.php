<?php

namespace Meare\Juggler\Test\Imposter\Stub\Response;


use InvalidArgumentException;
use Meare\Juggler\Imposter\Stub\Response\ProxyResponse;
use PHPUnit_Framework_TestCase;

class ProxyResponseTest extends PHPUnit_Framework_TestCase
{
    const VALID_TO = 'http://proxy:80';

    public function testInvalidUrlTo()
    {
        $this->expectException(InvalidArgumentException::class);
        new ProxyResponse('non-url');
    }

    public function testValidUrlWithPathTo()
    {
        $this->expectException(InvalidArgumentException::class);
        new ProxyResponse('http://proxy:80/path');
    }

    public function testSetValidTo()
    {
        new ProxyResponse(self::VALID_TO);
    }

    public function testSetValidMode()
    {
        $mode = ProxyResponse::MODE_PROXY_ALWAYS;
        $response = new ProxyResponse(self::VALID_TO, $mode);
        $this->assertEquals($mode, $response->getMode());
    }

    public function testInvalidMode()
    {
        $this->expectException(InvalidArgumentException::class);
        new ProxyResponse(self::VALID_TO, 'invalid_mode');
    }

    public function testCompile()
    {
        $to = self::VALID_TO;
        $mode = ProxyResponse::MODE_PROXY_ONCE;
        $predicate_generators = [['matches' => ['path' => true]]];
        $response = new ProxyResponse($to, $mode, $predicate_generators);
        $this->assertEquals([
            'proxy' => [
                'to'                  => $to,
                'mode'                => $mode,
                'predicateGenerators' => $predicate_generators,
            ],
        ], $response->jsonSerialize());
    }

    public function testStaticFactoryMethod()
    {
        $contract = ['to' => 'http://google.com'];
        $response = ProxyResponse::createFromContract($contract);
        $this->assertSame($contract['to'], $response->getTo());
    }

    public function testStaticFactoryMethodWithInvalidContract()
    {
        $this->expectException(InvalidArgumentException::class);
        $contract = ['mode' => ProxyResponse::MODE_PROXY_ALWAYS];
        ProxyResponse::createFromContract($contract);
    }
}
