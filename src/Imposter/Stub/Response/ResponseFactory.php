<?php

namespace Meare\Juggler\Imposter\Stub\Response;


use Meare\Juggler\Imposter\Stub\Injection;

class ResponseFactory
{
    /**
     * @var array
     */
    private $allowedTypes = [
        IResponse::TYPE_IS     => IsResponse::class,
        IResponse::TYPE_PROXY  => ProxyResponse::class,
        IResponse::TYPE_INJECT => Injection::class,
    ];

    /**
     * @param string       $type
     * @param array|string $contract
     * @return IResponse
     */
    public function createInstance($type, $contract)
    {
        if (!isset($this->allowedTypes[$type])) {
            throw new \InvalidArgumentException("Cannot create response object; Invalid response type: '$type'");
        }

        /** @var IResponse $class */
        $class = $this->allowedTypes[$type];
        return $class::createFromContract($contract);
    }
}