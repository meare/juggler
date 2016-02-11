<?php

namespace Meare\Juggler\Imposter\Stub\Predicate;


use Meare\Juggler\Imposter\Stub\Injection;

class PredicateFactory
{
    /**
     * @param string       $type
     * @param string|array $contract
     * @return IPredicate
     */
    public function createInstance(string $type, $contract) : IPredicate
    {
        if (!in_array($type, IPredicate::ALLOWED_OPERATORS)) {
            throw new \InvalidArgumentException("Cannot create predicate object; Invalid predicate type: '$type'");
        }

        if (IPredicate::INJECT === $type) {
            return $this->createInjection($contract);
        } else {
            return $this->createPredicate($type, $contract);
        }
    }

    /**
     * @param mixed $contract
     * @return Injection
     */
    private function createInjection($contract) : Injection
    {
        if (!is_string($contract)) {
            throw new \InvalidArgumentException('Cannot create predicate object; $contract must be string for "inject" predicate');
        }

        return Injection::createFromContract($contract);
    }

    /**
     * @param string $type
     * @param mixed  $contract
     * @return Predicate
     */
    private function createPredicate(string $type, $contract) : Predicate
    {
        if (!is_array($contract)) {
            throw new \InvalidArgumentException("Cannot create predicate object; $contract must be array for '$type' predicate");
        }

        return Predicate::createFromContract([$type => $contract]);
    }
}