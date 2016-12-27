<?php

namespace Meare\Juggler\Imposter\Builder;

use Meare\Juggler\Imposter\Imposter;

class AbstractImposterBuilder
{
    /**
     * @var array protocol-to-Builder map
     */
    protected $buildersMap = [
        Imposter::PROTOCOL_HTTP => HttpImposterBuilder::class,
    ];

    /**
     * Builds Imposter object from JSON contract using appropriate Builder
     *
     * @param string $json
     * @return \Meare\Juggler\Imposter\Imposter
     * @throws \InvalidArgumentException if contract has no protocol or no appropriate Builder found
     */
    public function build($json)
    {
        $contract = \GuzzleHttp\json_decode($json, true);
        if (!isset($contract['protocol'])) {
            throw new \InvalidArgumentException('Invalid contract; Protocol is not specified');
        }

        return $this->getBuilder($contract['protocol'])->build($contract);
    }

    /**
     * @param string $protocol
     * @return ImposterBuilder
     * @throws \InvalidArgumentException if no appropriate Builder found
     */
    protected function getBuilder($protocol)
    {
        if (!isset($this->buildersMap[$protocol])) {
            throw new \InvalidArgumentException("'$protocol' imposter objects are not supported");
        }

        return call_user_func([$this->buildersMap[$protocol], 'create']);
    }
}
