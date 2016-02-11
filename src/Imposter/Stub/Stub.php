<?php

namespace Meare\Juggler\Imposter\Stub;


use Meare\Juggler\Exception\Client\NotFoundException;
use Meare\Juggler\Imposter\Stub\Predicate\IPredicate;
use Meare\Juggler\Imposter\Stub\Response\IResponse;
use Meare\Juggler\Imposter\Stub\Response\IsResponse;
use Meare\Juggler\Imposter\Stub\Response\ProxyResponse;
use function Meare\Juggler\is_subarray_assoc;

class Stub implements \JsonSerializable
{
    /**
     * @var IResponse[]
     */
    private $responses = [];

    /**
     * @var IPredicate[]
     */
    private $predicates = [];

    /**
     * @param IResponse|IResponse[]        $responses
     * @param IPredicate|IPredicate[]|null $predicates
     */
    public function __construct($responses = [], $predicates = [])
    {
        if (is_array($responses)) {
            $this->addResponses($responses);
        } elseif (null !== $responses) {
            $this->addResponse($responses);
        }

        if (is_array($predicates)) {
            $this->addPredicates($predicates);
        } elseif (null !== $predicates) {
            $this->addPredicate($predicates);
        }
    }

    /**
     * @param IResponse[] $responses
     */
    private function addResponses(array $responses)
    {
        foreach ($responses as $response) {
            $this->addResponse($response);
        }
    }

    /**
     * @param IResponse $responses
     */
    private function addResponse(IResponse $responses)
    {
        $this->responses[] = $responses;
    }

    /**
     * @param IPredicate[] $predicates
     */
    private function addPredicates(array $predicates)
    {
        foreach ($predicates as $predicate) {
            $this->addPredicate($predicate);
        }
    }

    /**
     * @param IPredicate $predicate
     */
    private function addPredicate(IPredicate $predicate)
    {
        $this->predicates[] = $predicate;
    }

    /**
     * @return array
     */
    public function getPredicates() : array
    {
        return $this->predicates;
    }

    /**
     * @param array $match
     * @return bool
     */
    public function isPredicatesMatch(array $match) : bool
    {
        return is_subarray_assoc($match, $this->jsonSerializePredicates());
    }

    /**
     * @return array
     */
    private function jsonSerializePredicates() : array
    {
        $predicates = [];
        foreach ($this->predicates as $predicate) {
            $predicates[] = $predicate->jsonSerialize();
        }

        return $predicates;
    }

    public function clearPredicates()
    {
        $this->predicates = [];
    }

    public function clearResponses()
    {
        $this->responses = [];
    }

    /**
     * @return array
     */
    public function jsonSerialize() : array
    {
        return [
            'predicates' => $this->jsonSerializePredicates(),
            'responses'  => $this->jsonSerializeResponses(),
        ];
    }

    /**
     * @return array
     */
    private function jsonSerializeResponses() : array
    {
        $responses = [];
        foreach ($this->responses as $response) {
            $responses[] = $response->jsonSerialize();
        }

        return $responses;
    }

    /**
     * @return IResponse[]
     */
    public function getResponses()
    {
        return $this->responses;
    }

    /**
     * @param int $nth
     * @return IResponse
     * @throws \Meare\Juggler\Exception\Client\NotFoundException
     */
    public function getResponse(int $nth = 0) : IResponse
    {
        if (!$this->hasResponse($nth)) {
            throw new NotFoundException("Unable to find response at position $nth");
        }

        return $this->responses[$nth];
    }

    /**
     * @param int $index
     * @return bool
     */
    public function hasResponse(int $index) : bool
    {
        return isset($this->responses[$index]);
    }

    /**
     * Returns first or nth "is" response if exists
     *
     * @param int $nth
     * @return IsResponse
     */
    public function getIsResponse(int $nth = 0) : IsResponse
    {
        return $this->getResponseOfType(IResponse::TYPE_IS, $nth);
    }

    /**
     * Returns first or nth response of type ("is", "proxy" or "inject") if exists
     *
     * @param string $type
     * @param int    $nth number of $type-response in a list
     * @return IResponse
     * @throws NotFoundException
     */
    public function getResponseOfType(string $type, int $nth = 0) : IResponse
    {
        if (!in_array($type, IResponse::ALLOWED_TYPES)) {
            throw new \InvalidArgumentException("Unknown response type: '$type'");
        }
        $matches_found = 0;
        foreach ($this->responses as $response) {
            if ($response->getType() === $type && $matches_found++ === $nth) {
                return $response;
            }
        }
        throw new NotFoundException("Unable to find response of type '$type' at position $nth ($matches_found '$type' responses were found)");
    }

    /**
     * Returns first or nth "proxy" response if exists
     *
     * @param int $nth
     * @return ProxyResponse
     */
    public function getProxyResponse(int $nth = 0) : ProxyResponse
    {
        return $this->getResponseOfType(IResponse::TYPE_PROXY, $nth);
    }

    /**
     * Returns first or nth "inject" response if exists
     *
     * @param int $nth
     * @return Injection
     */
    public function getInjectionResponse(int $nth = 0) : Injection
    {
        return $this->getResponseOfType(IResponse::TYPE_INJECT, $nth);
    }
}