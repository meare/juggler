<?php

namespace Meare\Juggler\Imposter\Stub;


class StubBuilder
{
    /**
     * @var Response\ResponseFactory
     */
    private $responseFactory;

    /**
     * @var Predicate\PredicateFactory
     */
    private $predicateFactory;

    /**
     * @param Response\ResponseFactory   $responseFactory
     * @param Predicate\PredicateFactory $predicateFactory
     */
    public function __construct(Response\ResponseFactory $responseFactory, Predicate\PredicateFactory $predicateFactory)
    {
        $this->responseFactory = $responseFactory;
        $this->predicateFactory = $predicateFactory;
    }

    /**
     * @return StubBuilder
     */
    public static function create()
    {
        return new self(new Response\ResponseFactory, new Predicate\PredicateFactory);
    }

    /**
     * @param array $stub_contract
     * @return Stub
     */
    public function build(array $stub_contract)
    {
        if (!isset($stub_contract['responses'])) {
            throw new \InvalidArgumentException("Invalid contract: every stub should contain 'responses' field");
        }
        $responseObjects = $this->createResponses($stub_contract['responses']);
        $predicateObjects = $this->createPredicates(
            isset($stub_contract['predicates']) ? $stub_contract['predicates'] : []
        );

        return new Stub($responseObjects, $predicateObjects);
    }

    /**
     * @param array $response_contracts
     * @return Response\IResponse[]
     */
    private function createResponses(array $response_contracts)
    {
        $responseObjects = [];
        foreach ($response_contracts as $response_contract) {
            $type = key($response_contract);
            $contract = reset($response_contract);
            if (in_array($type, ['_behaviors'])) {
                continue;
            }

            $responseObjects[] = $this->responseFactory->createInstance($type, $contract);
        }

        return $responseObjects;
    }

    /**
     * @param array $predicate_contracts
     * @return Predicate\IPredicate[]
     */
    private function createPredicates(array $predicate_contracts)
    {
        $predicateObjects = [];
        foreach ($predicate_contracts as $predicate_contract) {
            $type = key($predicate_contract);
            $contract = reset($predicate_contract);

            $predicateObjects[] = $this->predicateFactory->createInstance($type, $contract);
        }

        return $predicateObjects;
    }
}