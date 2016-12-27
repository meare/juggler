<?php

namespace Meare\Juggler\Imposter\Builder;


use Meare\Juggler\Imposter\Imposter;

abstract class ImposterBuilder
{
    /**
     * @var string Expected contract protocol
     */
    protected $protocol;

    /**
     * Builds Imposter object from decoded contract
     *
     * @param array $contract
     * @return Imposter
     */
    abstract public function build(array $contract);

    /**
     * @param array $contract
     * @throws \InvalidArgumentException if contract protocol is not set or does not match expected
     */
    protected function validateContractProtocol(array $contract)
    {
        if (!isset($contract['protocol'])) {
            throw new \InvalidArgumentException('Unable to build Imposter; Invalid contract given; missing \'protocol\'');
        }
        if ($contract['protocol'] !== $this->protocol) {
            throw new \InvalidArgumentException("Unable to build Imposter; expected contract protocol to be '{$this->protocol}'; got '{$contract['protocol']}'");
        }
    }
}