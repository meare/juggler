<?php

namespace Meare\Juggler\Imposter\Builder;


use Meare\Juggler\Imposter\HttpImposter;
use Meare\Juggler\Imposter\Imposter;
use Meare\Juggler\Imposter\Stub\StubBuilder;

class HttpImposterBuilder extends ImposterBuilder
{
    /**
     * @var string
     */
    protected $protocol = Imposter::PROTOCOL_HTTP;

    /**
     * @var \Meare\Juggler\Imposter\Stub\StubBuilder
     */
    private $stubBuilder;

    /**
     * @param StubBuilder $stubBuilder
     */
    public function __construct(StubBuilder $stubBuilder)
    {
        $this->stubBuilder = $stubBuilder;
    }

    /**
     * @return HttpImposterBuilder
     */
    public static function create() : self
    {
        return new self(StubBuilder::create());
    }

    /**
     * @param array $contract
     * @return \Meare\Juggler\\Meare\Juggler\Imposter\Imposter
     */
    public function build(array $contract) : Imposter
    {
        $this->validateContractProtocol($contract);
        $imposter = new HttpImposter(
            $contract['port'] ?? null,
            $contract['requests'] ?? []
        );
        if (isset($contract['name'])) {
            $imposter->setName($contract['name']);
        }
        foreach ($contract['stubs'] ?? [] as $stub_contract) {
            $imposter->addStub($this->stubBuilder->build($stub_contract));
        }

        return $imposter;
    }
}