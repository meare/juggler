<?php

namespace Meare\Juggler\Imposter\Stub\Response;


/**
 * TODO add cert, key
 *
 * @package Meare\Juggler\Response
 */
class ProxyResponse implements IResponse
{
    const MODE_PROXY_ONCE = 'proxyOnce';
    const MODE_PROXY_ALWAYS = 'proxyAlways';

    /**
     * @var string
     */
    private $type = self::TYPE_PROXY;

    /**
     * @var string
     */
    private $to;

    /**
     * @var string
     */
    private $mode = self::MODE_PROXY_ONCE;

    /**
     * @var array
     */
    private $predicateGenerators;

    /**
     * @param string $to
     * @param string $mode
     * @param array  $predicate_generators
     */
    public function __construct($to, $mode = null, array $predicate_generators = [])
    {
        $this->setTo($to);
        if (null !== $mode) {
            $this->setMode($mode);
        }
        $this->setPredicateGenerators($predicate_generators);
    }

    /**
     * @param array $contract
     * @return ProxyResponse
     */
    public static function createFromContract($contract)
    {
        if (!isset($contract['to'])) {
            throw new \InvalidArgumentException("Cannot create ProxyResponse from contract: Invalid contract; 'to' field does not exists");
        }

        return new self(
            $contract['to'],
            isset($contract['mode']) ? $contract['mode'] : null,
            isset($contract['predicateGenerators']) ? $contract['predicateGenerators'] : []
        );
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            IResponse::TYPE_PROXY => [
                'to'                  => $this->getTo(),
                'mode'                => $this->getMode(),
                'predicateGenerators' => $this->getPredicateGenerators(),
            ],
        ];
    }

    /**
     * @return string
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * @param string $to
     */
    public function setTo($to)
    {
        if (!filter_var($to, FILTER_VALIDATE_URL) || null !== parse_url($to, PHP_URL_PATH)) {
            throw new \InvalidArgumentException(
                "Unable to set ProxyResponse 'to; 'to' value is not valid url without path"
            );
        }
        $this->to = $to;
    }

    /**
     * @return string
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * @param string $mode
     */
    public function setMode($mode)
    {
        if (!in_array($mode, [self::MODE_PROXY_ALWAYS, self::MODE_PROXY_ONCE])) {
            throw new \InvalidArgumentException(
                "Unable to set ProxyResponse mode; Allowed modes: '" . self::MODE_PROXY_ONCE . "', '" . self::MODE_PROXY_ONCE . "'"
            );
        }
        $this->mode = $mode;
    }

    /**
     * @return array
     */
    public function getPredicateGenerators()
    {
        return $this->predicateGenerators;
    }

    /**
     * @param array $predicate_generators
     */
    public function setPredicateGenerators(array $predicate_generators)
    {
        $this->predicateGenerators = $predicate_generators;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }
}
