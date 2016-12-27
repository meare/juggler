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
    public function createInstance($type, $contract)
    {
        if (IPredicate::OPERATOR_INJECT === $type) {
            return $this->createInjection($contract);
        } else {
            return $this->createPredicate($type, $contract);
        }
    }

    /**
     * @param mixed $contract
     * @return Injection
     */
    private function createInjection($contract)
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
    private function createPredicate($type, $contract)
    {
        if (!is_array($contract)) {
            throw new \InvalidArgumentException("Cannot create predicate object; $contract must be array for '$type' predicate");
        }

        return Predicate::createFromContract([$type => $contract]);
    }
}