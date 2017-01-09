<?php

namespace Meare\Juggler\Test;


use Meare\Juggler\Exception\Mountebank\NoSuchResourceException;
use Meare\Juggler\HttpClient\GuzzleClient;
use Meare\Juggler\Imposter\HttpImposter;
use Meare\Juggler\Juggler;
use PHPUnit_Framework_MockObject_MockObject;
use PHPUnit_Framework_TestCase;

class JugglerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var PHPUnit_Framework_MockObject_MockObject|GuzzleClient
     */
    private $httpClient;

    /**
     * @var string
     */
    private $host = 'mountebank';

    public function setUp()
    {
        $this->httpClient = $this->getMockBuilder(GuzzleClient::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testConstruction()
    {
        $host = 'mountebank';
        $port = 3535;

        $juggler = new Juggler($host, $port);

        $this->assertEquals($host, $juggler->getHost());
        $this->assertEquals($port, $juggler->getPort());
        $this->assertSame('http://mountebank:3535', $juggler->getUrl());
    }

    public function testPostImposterWithoutPort()
    {
        $imposter = new HttpImposter;
        $this->httpClient->expects($this->once())
            ->method('post')
            ->with('/imposters')
            ->willReturn('{"port": 4747}');

        $port = $this->getJuggler()->postImposter($imposter);

        $this->assertEquals(4747, $port);
        $this->assertEquals(4747, $imposter->getPort());
    }

    private function getJuggler() : Juggler
    {
        return new Juggler(
            $this->host,
            2525,
            $this->httpClient
        );
    }

    public function testGetImposterContractWithDefaultParams()
    {
        $this->httpClient->expects($this->once())
            ->method('get')
            ->with('/imposters/7575?');
        $juggler = $this->getJuggler();

        $juggler->getImposterContract(7575);
    }

    public function testDeleteImposter()
    {
        $this->httpClient->expects($this->once())
            ->method('delete')
            ->with('/imposters/6565?replayable=true&remove_proxies=true');
        $juggler = $this->getJuggler();

        $juggler->deleteImposter(6565, true, true);
    }

    public function testDeleteImposterWithDefaultParams()
    {
        $this->httpClient->expects($this->once())
            ->method('delete')
            ->with('/imposters/6565?');
        $juggler = $this->getJuggler();

        $juggler->deleteImposter(6565);
    }

    public function testDeleteImposters()
    {
        $this->httpClient->expects($this->once())
            ->method('delete')
            ->with('/imposters');
        $juggler = $this->getJuggler();

        $juggler->deleteImposters();
    }

    public function testRemoveProxies()
    {
        $this->httpClient->expects($this->once())
            ->method('get')
            ->with('/imposters/6565?remove_proxies=true');
        $juggler = $this->getJuggler();

        $juggler->removeProxies(6565);
    }

    public function testDeleteImposterReturnsContract()
    {
        $this->httpClient->method('delete')
            ->willReturn('{"foo":"bar"}');

        $this->assertEquals(
            '{"foo":"bar"}',
            $this->getJuggler()->deleteImposter(4545)
        );
    }

    public function testDeletingIfExists()
    {
        $this->httpClient->method('delete')
            ->willThrowException(new NoSuchResourceException('{}'));

        $this->assertNull($this->getJuggler()->deleteImposterIfExists(4545));
    }

    public function testDeleteImposterIfExistsReturnsContract()
    {
        $this->httpClient->method('delete')
            ->willReturn('{"foo":"bar"}');

        $this->assertEquals(
            '{"foo":"bar"}',
            $this->getJuggler()->deleteImposterIfExists(4545)
        );
    }
}
