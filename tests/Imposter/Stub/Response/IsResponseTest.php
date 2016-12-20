<?php

namespace Meare\Juggler\Test\Imposter\Stub\Response;


use InvalidArgumentException;
use Meare\Juggler\Imposter\Stub\Response\IsResponse;
use PHPUnit_Framework_TestCase;

class IsResponseTest extends PHPUnit_Framework_TestCase
{
    public function testInvalidMode()
    {
        $response = new IsResponse;
        $this->expectException(InvalidArgumentException::class);
        $response->setMode('invalid_mode');
    }

    public function testSetValidMode()
    {
        $response = new IsResponse;
        $mode = IsResponse::MODE_BINARY;
        $response->setMode($mode);
        $this->assertEquals($mode, $response->getMode());
    }

    public function testInvalidHeaderName()
    {
        $response = new IsResponse;
        $this->expectException(InvalidArgumentException::class);
        $response->setHeaders([
            1 => 'application/json',
        ]);
    }

    public function testStringHeaderValue()
    {
        $response = new IsResponse;
        $headers = [
            'Content-type' => 'application/json',
        ];
        $response->setHeaders($headers);
        $this->assertEquals($headers, $response->getHeaders());
    }

    public function testIntegerHeaderValue()
    {
        $response = new IsResponse;
        $headers = [
            'Content-type' => 1024,
        ];
        $response->setHeaders($headers);
        $this->assertEquals($headers, $response->getHeaders());
    }

    public function testSetStatusCode()
    {
        $response = new IsResponse;
        $status_code = 200;
        $response->setStatusCode($status_code);
        $this->assertEquals($status_code, $response->getStatusCode());
    }

    public function setInvalidBody()
    {
        $response = new IsResponse;
        $this->expectException(InvalidArgumentException::class);
        $response->setBody(2);
    }

    public function setStringBody()
    {
        $response = new IsResponse;
        $body = 'body';
        $response->setBody($body);
        $this->assertEquals($body, $response->getBody());
    }

    public function setArrayBody()
    {
        $response = new IsResponse;
        $body = ['field' => 'value'];
        $response->setBody($body);
        $this->assertEquals($body, $response->getBody());
    }

    public function testCompile()
    {
        $status_code = 200;
        $headers = ['Content-type' => 'application/json'];
        $body = 'Success';
        $mode = IsResponse::MODE_BINARY;
        $response = new IsResponse($status_code, $headers, $body, $mode);
        $this->assertEquals($response->jsonSerialize(), [
            'is' => [
                'statusCode' => $status_code,
                'headers'    => $headers,
                'body'       => $body,
                '_mode'      => $mode,
            ],
        ]);
    }

    public function testStaticFactoryMethod()
    {
        $contract = [
            'statusCode' => 404,
            'headers'    => [
                'Location' => 'http://google.com',
            ],
            'body'       => 'content',
            '_mode'      => IsResponse::MODE_TEXT,
        ];
        $response = IsResponse::createFromContract($contract);
        $this->assertSame($response->getStatusCode(), $contract['statusCode']);
        $this->assertSame($response->getHeaders(), $contract['headers']);
        $this->assertSame($response->getBody(), $contract['body']);
        $this->assertSame($response->getMode(), $contract['_mode']);
    }

    public function testStaticFactoryMethodWithInvalidContract()
    {
        $this->expectException(InvalidArgumentException::class);
        IsResponse::createFromContract([
            // missing statusCode
            'headers' => ['Content-type' => 'application/json'],
            'body'    => 'hey',
        ]);
    }

    public function testInvalidBody()
    {
        $this->expectException(InvalidArgumentException::class);

        new IsResponse(200, [], 123);
    }

    public function testModifyBody()
    {
        $response = new IsResponse(200, [], 'old body');

        $response->modifyBody(function (string $body) {
            return str_replace('old', 'new', $body);
        });

        $this->assertSame('new body', $response->getBody());
    }

    public function testModifyBodyWithoutReturn()
    {
        $response = new IsResponse(200, [], 'old body');

        $this->expectException(\LogicException::class);

        $response->modifyBody(function () {
        });
    }
}
