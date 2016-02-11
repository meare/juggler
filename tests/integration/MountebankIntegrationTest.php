<?php

namespace Meare\Juggler\Test\Integration;


use Meare\Juggler\HttpClient\IHttpClient;
use Meare\Juggler\Juggler;
use PHPUnit_Framework_MockObject_MockObject;

class MountebankIntegrationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var null|IHttpClient|PHPUnit_Framework_MockObject_MockObject
     */
    private $httpClient;

    public function setUp()
    {
        $this->httpClient = null;
    }

    /**
     * @covers Meare\Juggler\Juggler::createImposterFromFile
     */
    public function testRecreatingHttpImposter()
    {
        $path = __DIR__ . '/contracts/http_imposter.json';
        $pretty_json = json_encode(json_decode(file_get_contents($path)), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        $imposter = $this->getJuggler()->createImposterFromFile($path);

        $this->assertSame(
            $pretty_json,
            json_encode($imposter, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
            'Imposter object compiles into source JSON contract'
        );
    }

    /**
     * @return Juggler
     */
    private function getJuggler() : Juggler
    {
        return new Juggler('localhost', 2525, $this->httpClient);
    }

    /**
     * @covers Meare\Juggler\Juggler::getImposter
     */
    public function testGetImposter()
    {
        $contract = json_encode([
            'port'     => 4646,
            'protocol' => 'http',
            'name'     => 'test imposter',
        ]);
        $this->httpClient = $this->getMockBuilder(IHttpClient::class)->getMock();
        $this->httpClient->expects($this->once())
            ->method('get')
            ->with('/imposters/4545?replayable=1&remove_proxies=1')
            ->willReturn($contract);

        $imposter = $this->getJuggler()->getImposter(4545, true, true);

        $this->assertEquals(4646, $imposter->getPort());
        $this->assertEquals('http', $imposter->getProtocol());
        $this->assertEquals('test imposter', $imposter->getName());
    }
}
