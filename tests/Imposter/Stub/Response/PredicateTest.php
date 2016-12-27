<?php

namespace Meare\Juggler\Test\Imposter\Stub\Response;


use InvalidArgumentException;
use Meare\Juggler\Imposter\Stub\Predicate\Predicate;
use PHPUnit_Framework_TestCase;

class PredicateTest extends PHPUnit_Framework_TestCase
{
    public function testInvalidOperator()
    {
        $this->expectException(InvalidArgumentException::class);

        new Predicate('invalid', []);
    }

    public function testCompilation()
    {
        $operator = Predicate::OPERATOR_AND;
        $fields = [
            'body' => 'value',
        ];

        $predicate = new Predicate($operator, $fields);

        $this->assertEquals([
            $operator => $fields,
        ], $predicate->jsonSerialize());
    }

    public function testEmptyObjectPredicate()
    {
        $predicate = new Predicate(Predicate::OPERATOR_DEEP_EQUALS, []);

        $this->assertSame('{"deepEquals":{}}', json_encode($predicate));
    }

    public function testEmptyArrayPredicate()
    {
        $predicate = new Predicate(Predicate::OPERATOR_AND, []);

        $this->assertSame('{"and":[]}', json_encode($predicate));
    }

    public function testEmptyDeepEqualsQuery()
    {
        $predicate = new Predicate(Predicate::OPERATOR_DEEP_EQUALS, ['query' => []]);

        $this->assertSame('{"deepEquals":{"query":{}}}', json_encode($predicate));
    }

    public function testGetOperator()
    {
        $predicate = new Predicate(Predicate::OPERATOR_DEEP_EQUALS, []);

        $this->assertSame(Predicate::OPERATOR_DEEP_EQUALS, $predicate->getOperator());
    }

    public function testInvalidPredicateType()
    {
        $this->expectException(InvalidArgumentException::class);
        
        new Predicate('invalidType', []);
    }
}
