<?php

namespace Meare\Juggler\Imposter;


use Meare\Juggler\Exception\Client\NotFoundException;

abstract class Imposter implements \JsonSerializable
{
    const PROTOCOL_HTTPS = 'https';
    const PROTOCOL_HTTP = 'http';
    const PROTOCOL_TCP = 'tcp';
    const PROTOCOL_SMTP = 'smtp';

    /**
     * @var string
     */
    protected $protocol;

    /**
     * @var array
     */
    protected $requests;

    /**
     * @var int|null
     */
    private $port;

    /**
     * @var string
     */
    private $name;

    /**
     * @param int|null $port
     * @param array    $requests
     */
    public function __construct($port = null, array $requests = [])
    {
        if (null !== $port) {
            $this->setPort($port);
        }

        $this->setRequests($requests);
    }

    /**
     * @param array $requests
     */
    private function setRequests(array $requests)
    {
        $this->requests = $requests;
    }

    /**
     * @return bool
     */
    public function hasRequests()
    {
        return sizeof($this->requests) > 0;
    }

    /**
     * @return array
     */
    public function getRequests()
    {
        return $this->requests;
    }

    /**
     * @param array|callable $criteria
     * @param int            $exactly Expect exactly n occurrences
     * @return bool
     * @throws NotFoundException
     */
    public function hasRequestsByCriteria($criteria, $exactly = null)
    {
        $num = $this->countRequestsByCriteria($criteria);
        if (null === $exactly) {
            return $num > 0;
        } else {
            return $num === $exactly;
        }
    }

    /**
     * @param array|callable $criteria
     * @return int
     */
    public function countRequestsByCriteria($criteria)
    {
        try {
            return sizeof($this->findRequests($criteria));
        } catch (NotFoundException $e) {
            return 0;
        }
    }

    /**
     * @param array|callable $criteria
     * @return array[]
     * @throws NotFoundException
     * @throws \InvalidArgumentException
     */
    public function findRequests($criteria)
    {
        switch (true) {
            case is_array($criteria):
                $matched_requests = $this->findRequestsWithSubarray($criteria);
                break;

            case is_callable($criteria):
                $matched_requests = $this->findRequestsWithCallable($criteria);
                break;

            default:
                throw new \InvalidArgumentException('Criteria could only be array or callable');
        }

        if (0 === sizeof($matched_requests)) {
            throw new NotFoundException('Unable to find any requests per criteria');
        }

        return $matched_requests;
    }

    /**
     * @param array $criteria
     * @return array
     */
    private function findRequestsWithSubarray($criteria)
    {
        $matched_requests = [];
        foreach ($this->requests as $request) {
            if (\Meare\Juggler\is_subarray_assoc($criteria, $request)) {
                $matched_requests[] = $request;
            }
        }

        return $matched_requests;
    }

    /**
     * @param callback $callback
     * @return array
     */
    private function findRequestsWithCallable($callback)
    {
        $matched_requests = [];
        foreach ($this->requests as $request) {
            if (true === $callback($request)) {
                $matched_requests[] = $request;
            }
        }

        return $matched_requests;
    }

    /**
     * @return int|null
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @param int $port
     */
    public function setPort($port)
    {
        $this->port = $port;
    }

    /**
     * @return string
     */
    public function getProtocol()
    {
        return $this->protocol;
    }

    /**
     * @return bool
     */
    public function hasName()
    {
        return null !== $this->getName();
    }

    /**
     * @return string|null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return bool
     */
    public function hasPort()
    {
        return null !== $this->port;
    }
}
