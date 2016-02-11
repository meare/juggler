<?php

namespace Meare\Juggler\Imposter;


use Meare\Juggler\Exception\Client\NotFoundException;
use Meare\Juggler\Imposter\Stub\Predicate\IPredicate;
use Meare\Juggler\Imposter\Stub\Response\IResponse;
use function Meare\Juggler\array_filter_null;
use function Meare\Juggler\is_subarray_assoc;
use function Meare\Juggler\json_object;

class HttpImposter extends Imposter
{
    /**
     * @var string
     */
    protected $protocol = self::PROTOCOL_HTTP;

    /**
     * @var Stub\Stub[]
     */
    private $stubs = [];

    /**
     * @return array
     */
    public function jsonSerialize() : array
    {
        return array_filter_null([
            'port'     => $this->getPort(),
            'protocol' => $this->getProtocol(),
            'name'     => $this->getName(),
            'requests' => $this->hasRequests() ? $this->serializeRequests() : null,
            'stubs'    => $this->jsonSerializeStubs(),
        ]);
    }

    /**
     * @return array
     */
    private function serializeRequests() : array
    {
        $requests = $this->requests;
        foreach ($requests as $key => $request) {
            if (isset($request['query'])) {
                $requests[$key]['query'] = json_object($request['query']);
            }
            if (isset($request['headers'])) {
                $requests[$key]['headers'] = json_object($request['headers']);
            }
        }

        return $requests;
    }

    /**
     * @return array
     */
    private function jsonSerializeStubs() : array
    {
        $stubs = [];
        foreach ($this->stubs as $stub) {
            $stubs[] = $stub->jsonSerialize();
        }

        return $stubs;
    }

    /**
     * @param IResponse[]|IResponse   $responses
     * @param IPredicate[]|IPredicate $predicates
     * @return Stub\Stub
     */
    public function createStub($responses = null, $predicates = null) : Stub\Stub
    {
        $stub = new Stub\Stub($responses, $predicates);
        $this->addStub($stub);

        return $stub;
    }

    /**
     * @param Stub\Stub $stub
     */
    public function addStub(Stub\Stub $stub)
    {
        $this->stubs[] = $stub;
    }

    /**
     * @return Stub\Stub[]
     */
    public function getStubs() : array
    {
        return $this->stubs;
    }

    /**
     * @param Stub\Stub[] $stubs
     */
    public function setStubs(array $stubs)
    {
        $this->clearStubs();
        $this->addStubs($stubs);
    }

    public function clearStubs()
    {
        $this->stubs = [];
    }

    /**
     * @param Stub\Stub[] $stubs
     */
    public function addStubs(array $stubs)
    {
        foreach ($stubs as $stub) {
            $this->addStub($stub);
        }
    }

    /**
     * @param Stub\Stub $stub
     * @throws NotFoundException
     */
    public function removeStub(Stub\Stub $stub)
    {
        foreach ($this->stubs as $key => $s) {
            if ($s === $stub) {
                unset($this->stubs[$key]);

                return;
            }
        }
        throw new NotFoundException('Unable to find stub');
    }

    /**
     * @param array $criteria
     * @return Stub\Stub
     * @throws NotFoundException
     */
    public function findStubByPredicates($criteria) : Stub\Stub
    {
        foreach ($this->stubs as $stub) {
            if ($stub->isPredicatesMatch($criteria)) {
                return $stub;
            }
        }
        throw new NotFoundException('Unable to find stub');
    }
}
