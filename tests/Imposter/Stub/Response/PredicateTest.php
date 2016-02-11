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
        $operator = Predicate:: AND;
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
        $predicate = new Predicate(Predicate::DEEP_EQUALS, []);

        $this->assertSame('{"deepEquals":{}}', json_encode($predicate));
    }

    public function testEmptyArrayPredicate()
    {
        $predicate = new Predicate(Predicate:: AND, []);

        $this->assertSame('{"and":[]}', json_encode($predicate));
    }

    public function testEmptyDeepEqualsQuery()
    {
        $predicate = new Predicate(Predicate::DEEP_EQUALS, ['query' => []]);

        $this->assertSame('{"deepEquals":{"query":{}}}', json_encode($predicate));
    }

    public function testGetOperator()
    {
        $predicate = new Predicate(Predicate::DEEP_EQUALS, []);

        $this->assertSame(Predicate::DEEP_EQUALS, $predicate->getOperator());
    }
}
