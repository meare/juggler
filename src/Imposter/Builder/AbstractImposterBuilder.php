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
    public function build(string $json) : Imposter
    {
        $contract = \GuzzleHttp\json_decode($json, true);
        $protocol = $contract['protocol'] ?? null;
        if (null === $protocol) {
            throw new \InvalidArgumentException('Invalid contract; Protocol is not specified');
        }

        return $this->getBuilder($protocol)->build($contract);
    }

    /**
     * @param string $protocol
     * @return ImposterBuilder
     * @throws \InvalidArgumentException if no appropriate Builder found
     */
    protected function getBuilder(string $protocol) : ImposterBuilder
    {
        $builder = $this->buildersMap[$protocol] ?? null;
        if (null === $builder) {
            throw new \InvalidArgumentException("'$protocol' imposter objects are not supported");
        }

        return call_user_func([$this->buildersMap[$protocol], 'create']);
    }
}
