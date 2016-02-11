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
    public function createInstance(string $type, $contract) : IResponse
    {
        $class = $this->allowedTypes[$type] ?? null;
        if (null === $class) {
            throw new \InvalidArgumentException("Cannot create response object; Invalid response type: '$type'");
        }

        /** @var IResponse $class */
        return $class::createFromContract($contract);
    }
}