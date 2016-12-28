<?php


namespace Meare\Juggler\Test\Imposter;


use Meare\Juggler\Exception\Client\NotFoundException;
use Meare\Juggler\Imposter\HttpImposter;
use Meare\Juggler\Imposter\Imposter;
use Meare\Juggler\Imposter\Stub\Injection;
use Meare\Juggler\Imposter\Stub\Predicate\Predicate;
use Meare\Juggler\Imposter\Stub\Response\IsResponse;
use Meare\Juggler\Imposter\Stub\Stub;

class HttpImposterTest extends \PHPUnit_Framework_TestCase
{
    public function testAddInvalidStubs()
    {
        $this->setExpectedException(\TypeError::class);
        (new HttpImposter)->addStubs([
            'invalid',
        ]);
    }

    public function testAddStub()
    {
        $imposter = new HttpImposter;

        $stub = new Stub(new IsResponse);
        $imposter->addStub($stub);

        $this->assertSame([$stub], $imposter->getStubs());
    }

    public function testSetStubs()
    {
        $imposter = new HttpImposter;

        $imposter->addStub(new Stub(new IsResponse));
        $stub = new Stub(new Injection('return;'));
        $imposter->setStubs([$stub]);

        $this->assertSame([$stub], $imposter->getStubs());
    }

    public function testRemovingStub()
    {
        $imposter = new HttpImposter;

        $stub1 = new Stub(new IsResponse);
        $imposter->addStub($stub1);
        $stub2 = new Stub(new Injection('return;'));
        $imposter->addStub($stub2);
        $imposter->removeStub($stub1);

        $this->assertSame([$stub2], array_values($imposter->getStubs()));
    }

    public function testJsonSerializeWithOptionalFields()
    {
        $requests = [['body' => '']];
        $imposter = new HttpImposter(4545, $requests);
        $imposter_name = 'ServiceStub';
        $imposter->setName($imposter_name);
        $imposter->addStub(new Stub);

        $this->assertSame([
            'port'     => 4545,
            'protocol' => Imposter::PROTOCOL_HTTP,
            'name'     => $imposter_name,
            'requests' => $requests,
            'stubs'    => [
                ['predicates' => [], 'responses' => []],
            ],
        ], $imposter->jsonSerialize());
    }

    public function testJsonSerializeWithoutOptionalField()
    {
        $imposter = new HttpImposter(4545);

        $this->assertSame([
            'port'     => 4545,
            'protocol' => Imposter::PROTOCOL_HTTP,
            'stubs'    => [],
        ], $imposter->jsonSerialize());
    }

    public function testGetStubByPredicateMatch()
    {
        $imposter = new HttpImposter;
        $getStub = new Stub([], [
            new Predicate(Predicate::OPERATOR_DEEP_EQUALS, ['method' => 'GET']),
            new Predicate(Predicate::OPERATOR_DEEP_EQUALS, ['path' => '/counters']),
        ]);
        $postStub = new Stub([], [
            new Predicate(Predicate::OPERATOR_DEEP_EQUALS, ['method' => 'POST']),
            new Predicate(Predicate::OPERATOR_DEEP_EQUALS, ['path' => '/counters']),
        ]);

        $imposter->setStubs([$getStub, $postStub]);

        $this->assertSame($postStub, $imposter->findStubByPredicates([
            ['deepEquals' => ['method' => 'POST']],
            ['deepEquals' => ['path' => '/counters']],
        ]));
    }

    public function testStubByPredicateNotFound()
    {
        $this->setExpectedException(NotFoundException::class);

        (new HttpImposter)->findStubByPredicates([
            ['deepEquals' => ['method' => 'POST']],
        ]);
    }

    public function testCreateStub()
    {
        $response = new IsResponse;
        $predicate = new Predicate(Predicate::OPERATOR_DEEP_EQUALS, ['body' => '']);

        $stub = (new HttpImposter(null))->createStub($response, $predicate);

        $this->assertSame([$response], $stub->getResponses());
        $this->assertSame([$predicate], $stub->getPredicates());
    }

    public function testRemoveInvalidStub()
    {
        $stub = new Stub;
        $imposter = new HttpImposter;

        $this->setExpectedException(NotFoundException::class);
        $imposter->removeStub($stub);
    }

    public function testEmptyRequestHeadersSerializesAsStdClass()
    {
        $imposter = new HttpImposter(null, [['headers' => []]]);

        $this->assertInstanceOf(\stdClass::class, $imposter->jsonSerialize()['requests'][0]['headers']);
    }

    public function testEmptyRequestQuerySerializesAsStdClass()
    {
        $imposter = new HttpImposter(null, [['query' => []]]);

        $this->assertInstanceOf(\stdClass::class, $imposter->jsonSerialize()['requests'][0]['query']);
    }

    public function testJsonSerializeWithMinimumFieldsSet()
    {
        $this->assertSame([
            'protocol' => 'http',
            'stubs'    => [],
        ], (new HttpImposter)->jsonSerialize());
    }
}
