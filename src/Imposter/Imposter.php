<?php

namespace Meare\Juggler\Imposter;


use Meare\Juggler\Exception\Client\NotFoundException;
use function Meare\Juggler\is_subarray_assoc;

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
    public function __construct(int $port = null, array $requests = [])
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
    public function hasRequests() : bool
    {
        return sizeof($this->requests) > 0;
    }

    /**
     * @return array
     */
    public function getRequests() : array
    {
        return $this->requests;
    }

    /**
     * @param array $criteria
     * @param int   $exactly Expect exactly n occurrences
     * @return bool
     * @throws NotFoundException
     */
    public function hasRequestsByCriteria($criteria, int $exactly = null) : bool
    {
        $num = $this->countRequestsByCriteria($criteria);
        if (null === $exactly) {
            return $num > 0;
        } else {
            return $num === $exactly;
        }
    }

    /**
     * @param array $criteria
     * @return int
     */
    public function countRequestsByCriteria($criteria) : int
    {
        try {
            return sizeof($this->findRequests($criteria));
        } catch (NotFoundException $e) {
            return 0;
        }
    }

    /**
     * @param array $match
     * @return array
     * @throws NotFoundException
     */
    public function findRequests($match) : array
    {
        $matched_requests = [];
        foreach ($this->requests as $request) {
            if (is_subarray_assoc($match, $request)) {
                $matched_requests[] = $request;
            }
        }
        if (sizeof($matched_requests) > 0) {
            return $matched_requests;
        }
        throw new NotFoundException('Unable to find any requests');
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
     * @return self
     */
    public function setPort(int $port)
    {
        $this->port = $port;
    }

    /**
     * @return string
     */
    public function getProtocol() : string
    {
        return $this->protocol;
    }

    /**
     * @return bool
     */
    public function hasName() : bool
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
    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return bool
     */
    public function hasPort() : bool
    {
        return null !== $this->port;
    }
}
