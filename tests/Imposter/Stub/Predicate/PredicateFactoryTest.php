<?php

namespace Meare\Juggler\Test\Imposter\Stub\Predicate;


use InvalidArgumentException;
use Meare\Juggler\Imposter\Stub\Injection;
use Meare\Juggler\Imposter\Stub\Predicate\Predicate;
use Meare\Juggler\Imposter\Stub\Predicate\PredicateFactory;
use PHPUnit_Framework_TestCase;

class PredicateFactoryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \Meare\Juggler\Imposter\Stub\Predicate\PredicateFactory
     */
    private $factory;

    public function setUp()
    {
        $this->factory = new PredicateFactory;
    }

    public function testCreateInject()
    {
        $this->factory = new PredicateFactory;
        $this->assertInstanceOf(Injection::class, $this->factory->createInstance('inject', 'function(){}'));
    }

    public function testCreatePredicate()
    {
        $this->factory = new PredicateFactory;
        $this->assertInstanceOf(Predicate::class, $this->factory->createInstance('deepEquals', ['statusCode' => 404]));
    }

    public function testInvalidPredicateType()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->factory->createInstance('invalidType', []);
    }

    public function testInjectWithArrayContract()
    {
        $this->expectException(InvalidArgumentException::class);

        $this->factory->createInstance(Predicate::INJECT, []);
    }

    public function testEqualsWithStringContract()
    {
        $this->expectException(InvalidArgumentException::class);

        $this->factory->createInstance(Predicate::EQUALS, 'predicate');
    }
}
