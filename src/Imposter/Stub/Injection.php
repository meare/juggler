<?php

namespace Meare\Juggler\Imposter\Stub;


use Meare\Juggler\Imposter\Stub\Predicate\IPredicate;
use Meare\Juggler\Imposter\Stub\Response\IResponse;

class Injection implements IResponse, IPredicate
{
    /**
     * @var string
     */
    private $type = self::TYPE_INJECT;

    /**
     * @var string
     */
    private $js;

    public function __construct($js)
    {
        $this->js = $js;
    }

    /**
     * @param string $js Contract is JS string injection in case of 'inject'
     * @return Injection
     */
    public static function createFromContract($js)
    {
        return new self($js);
    }

    /**
     * @return string
     */
    public function getJs()
    {
        return $this->js;
    }

    /**
     * @param string $js
     */
    public function setJs($js)
    {
        $this->js = $js;
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            IResponse::TYPE_INJECT => $this->js,
        ];
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }
}