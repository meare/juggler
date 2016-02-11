<?php

namespace Meare\Juggler\Test\Imposter\Stub;


use Meare\Juggler\Exception\Client\NotFoundException;
use Meare\Juggler\Imposter\Stub\Injection;
use Meare\Juggler\Imposter\Stub\Predicate\Predicate;
use Meare\Juggler\Imposter\Stub\Response\IsResponse;
use Meare\Juggler\Imposter\Stub\Response\ProxyResponse;
use Meare\Juggler\Imposter\Stub\Stub;
use PHPUnit_Framework_TestCase;

class StubTest extends PHPUnit_Framework_TestCase
{
    public function testConstructingWithSingleResponse()
    {
        $response = new IsResponse(201);
        $stub = new Stub($response);
        $this->assertEquals([
            'predicates' => [],
            'responses'  => [
                [
                    'is' => [
                        'statusCode' => 201,
                        'headers'    => [],
                        'body'       => '',
                        '_mode'      => 'text',
                    ],
                ],
            ],
        ], $stub->jsonSerialize());
    }

    public function testConstructingWithMultipleResponses()
    {
        $stub = new Stub([
            new IsResponse(201),
            new IsResponse(404),
        ]);
        $this->assertEquals([
            'predicates' => [],
            'responses'  => [
                [
                    'is' => [
                        'statusCode' => 201,
                        'headers'    => [],
                        'body'       => '',
                        '_mode'      => 'text',
                    ],
                ],
                [
                    'is' => [
                        'statusCode' => 404,
                        'headers'    => [],
                        'body'       => '',
                        '_mode'      => 'text',
                    ],
                ],
            ],
        ], $stub->jsonSerialize());
    }

    public function testInvalidResponses()
    {
        $this->expectException(\TypeError::class);
        new Stub(['not_a_Response_instance']);
    }

    public function testConstructingWithPredicate()
    {
        $stub = new Stub(
            new IsResponse,
            new Predicate(Predicate::CONTAINS, [
                'body' => 'data',
            ])
        );
        $this->assertEquals([
            'responses'  => [
                [
                    'is' => [
                        'statusCode' => 200,
                        'headers'    => [],
                        'body'       => '',
                        '_mode'      => 'text',
                    ],
                ],
            ],
            'predicates' => [
                [
                    'contains' => [
                        'body' => 'data',
                    ],
                ],
            ],
        ], $stub->jsonSerialize());
    }

    public function testInjectionResponse()
    {
        $js = 'function (request, state, logger callback) { ...';
        $stub = new Stub(new Injection($js));
        $this->assertEquals([
            'predicates' => [],
            'responses'  => [
                ['inject' => $js],
            ],
        ], $stub->jsonSerialize());
    }

    public function testInjectionPredicate()
    {
        $js = 'function (request, logger) {..';
        $stub = new Stub(new IsResponse, new Injection($js));
        $this->assertEquals([
            'responses'  => [
                [
                    'is' => [
                        'statusCode' => 200,
                        'headers'    => [],
                        'body'       => '',
                        '_mode'      => 'text',
                    ],
                ],
            ],
            'predicates' => [
                [
                    'inject' => $js,
                ],
            ],
        ], $stub->jsonSerialize());
    }

    public function testGetResponseByType()
    {
        $is = new IsResponse;
        $proxy = new ProxyResponse('http://google.com');
        $inject1 = new Injection('function(){}');
        $inject2 = new Injection('function(){}');
        $stub = new Stub([$is, $proxy, $inject1, $inject2]);
        $this->assertSame($is, $stub->getIsResponse());
        $this->assertSame($proxy, $stub->getProxyResponse());
        $this->assertSame($inject1, $stub->getInjectionResponse());
        $this->assertSame($inject2, $stub->getInjectionResponse(1));
    }

    public function testGetResponseByInvalidType()
    {
        $stub = new Stub;
        $this->expectException(\InvalidArgumentException::class);
        $stub->getResponseOfType('non-existing-type');
    }

    public function testResponseOfTypeNotFound()
    {
        $stub = new Stub(new IsResponse);
        $this->expectException(NotFoundException::class);
        $stub->getProxyResponse();
    }

    public function testResponseOfTypeNotFoundAtIndex()
    {
        $stub = new Stub([new IsResponse, new Injection('http://google.com')]);
        $this->expectException(NotFoundException::class);
        $stub->getIsResponse(1);
    }

    public function testGetResponses()
    {
        $responses = [new IsResponse, new IsResponse()];
        $stub = new Stub($responses);
        $this->assertSame($responses, $stub->getResponses());
    }

    public function testHasResponse()
    {
        $responses = [new IsResponse, new IsResponse()];
        $stub = new Stub($responses);
        $this->assertTrue($stub->hasResponse(1));
        $this->assertFalse($stub->hasResponse(2));
    }

    public function testGetPredicates()
    {
        $predicate = new Predicate(Predicate::DEEP_EQUALS, ['body' => '']);
        $stub = new Stub(null, $predicate);

        $this->assertSame([$predicate], $stub->getPredicates());
    }

    public function testClearResponses()
    {
        $stub = new Stub([new IsResponse], []);

        $stub->clearResponses();

        $this->assertSame([], $stub->getResponses());
    }

    public function testClearPredicates()
    {
        $stub = new Stub([], [new Predicate(Predicate::DEEP_EQUALS, [])]);

        $stub->clearPredicates();

        $this->assertSame([], $stub->getPredicates());
    }

    public function testGetResponse()
    {
        $response = new IsResponse;
        $stub = new Stub([new IsResponse, $response], []);

        $this->assertSame($response, $stub->getResponse(1));
    }

    public function testGetInvalidResponse()
    {
        $stub = new Stub([], []);

        $this->expectException(NotFoundException::class);
        $stub->getResponse(2);
    }
}
