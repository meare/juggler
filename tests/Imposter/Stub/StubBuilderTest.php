<?php

namespace Meare\Juggler\Test\Imposter\Stub;


use Meare\Juggler\Imposter\Stub\Predicate\IPredicate;
use Meare\Juggler\Imposter\Stub\Predicate\PredicateFactory;
use Meare\Juggler\Imposter\Stub\Response\IResponse;
use Meare\Juggler\Imposter\Stub\Response\ResponseFactory;
use Meare\Juggler\Imposter\Stub\StubBuilder;
use PHPUnit_Framework_TestCase;

class StubBuilderTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var ResponseFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $responseFactory;

    /**
     * @var PredicateFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $predicateFactory;

    public function setUp()
    {
        $this->responseFactory = $this->getMockBuilder(ResponseFactory::class)
            ->getMock();
        $this->responseFactory
            ->method('createInstance')
            ->willReturn($this->getMockBuilder(IResponse::class)->getMock());

        $this->predicateFactory = $this->getMockBuilder(PredicateFactory::class)
            ->getMock();
        $this->predicateFactory
            ->method('createInstance')
            ->willReturn($this->getMockBuilder(IPredicate::class)->getMock());
    }

    public function testResponseFactoryIsCalled()
    {
        $responses = [
            ['statusCode' => 404, 'body' => 'text'],
            ['to' => 'http://google.com'],
        ];
        $this->responseFactory->expects($this->exactly(2))
            ->method('createInstance')
            ->withConsecutive(['is', $responses[0]], ['proxy', $responses[1]]);

        $this->getStubBuilder()->build([
            'responses' => [
                ['is' => $responses[0]],
                ['proxy' => $responses[1]],
            ],
        ]);
    }

    private function getStubBuilder()
    {
        return new StubBuilder($this->responseFactory, $this->predicateFactory);
    }

    public function testPredicateFactoryIsCalled()
    {
        $predicates = [
            ['body' => 'value'],
            'function(){}',
        ];
        $this->predicateFactory->expects($this->exactly(2))
            ->method('createInstance')
            ->withConsecutive(['equals', $predicates[0]], ['inject', $predicates[1]]);

        $this->getStubBuilder()->build([
            'responses'  => [
                ['is' => ['statusCode' => 404]],
            ],
            'predicates' => [
                ['equals' => $predicates[0]],
                ['inject' => $predicates[1]],
            ],
        ]);
    }

    public function testStubWithoutResponseCausesException()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->getStubBuilder()->build([
            'predicates' => [
                ['equals' => ['statusCode' => 500]],
            ],
        ]);
    }

    /**
     * @covers Meare\Juggler\Imposter\Stub\StubBuilder::build
     */
    public function testIntegration()
    {
        $stubBuilder = new StubBuilder(new ResponseFactory, new PredicateFactory);
        $stub_contract = [
            'responses'  => [
                ['is' => ['statusCode' => 404]],
            ],
            'predicates' => [
                ['equals' => ['body' => 'value']],
                ['inject' => 'function(){}'],
            ],
        ];

        $stub = $stubBuilder->build($stub_contract);

        $this->assertArraySubset($stub_contract, $stub->jsonSerialize());
    }

    public function testBehaviorsAreSkipped()
    {
        $stubBuilder = new StubBuilder(new ResponseFactory, new PredicateFactory);
        $stub_contract = [
            'responses' => [
                ['_behaviors' => ['wait' => 500]],
            ],
        ];

        $stub = $stubBuilder->build($stub_contract);

        $this->assertEquals(['responses' => [], 'predicates' => []], $stub->jsonSerialize());
    }

    public function testStaticFactory()
    {
        $this->assertInstanceOf(StubBuilder::class, StubBuilder::create());
    }
}
