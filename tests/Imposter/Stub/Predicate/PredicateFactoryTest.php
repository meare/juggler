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

    public function testInjectWithArrayContract()
    {
        $this->setExpectedException(InvalidArgumentException::class);

        $this->factory->createInstance(Predicate::OPERATOR_INJECT, []);
    }

    public function testEqualsWithStringContract()
    {
        $this->setExpectedException(InvalidArgumentException::class);

        $this->factory->createInstance(Predicate::OPERATOR_EQUALS, 'predicate');
    }
}
