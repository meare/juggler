<?php

namespace Meare\Juggler\Test\Integration;


use Meare\Juggler\HttpClient\IHttpClient;
use Meare\Juggler\Imposter\HttpImposter;
use Meare\Juggler\Juggler;
use org\bovigo\vfs\vfsStream;
use PHPUnit_Framework_MockObject_MockObject;

class JugglerIntegrationTest extends \PHPUnit_Framework_TestCase
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
     * @covers \Meare\Juggler\Juggler::createImposterFromFile
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

    public function testPostingContractThatDoesNotExist()
    {
        vfsStream::setup('home');
        $this->setExpectedException(\RuntimeException::class);

        $this->getJuggler()->postImposterFromFile(vfsStream::url('home/foo/bar.json'));
    }

    public function testCreatingImposterFromFileThatDoesNotExists()
    {
        vfsStream::setup('home');
        $this->setExpectedException(\RuntimeException::class);

        $this->getJuggler()->createImposterFromFile(vfsStream::url('home/foo/bar.json'));
    }

    public function testCreateImposterFromFile()
    {
        vfsStream::setup('home');
        file_put_contents(
            vfsStream::url('home/contract.json'),
            '{"protocol":"http","stubs":[],"name":"foo bar"}'
        );

        $imposter = $this->getJuggler()->createImposterFromFile(vfsStream::url('home/contract.json'));

        $this->assertSame('foo bar', $imposter->getName());
    }

    public function testCreateImposterFromInvalidContract()
    {
        vfsStream::setup('home');
        file_put_contents(
            vfsStream::url('home/contract.json'),
            'invalid json'
        );

        $this->setExpectedException(\InvalidArgumentException::class);

        $this->getJuggler()->createImposterFromFile(vfsStream::url('home/contract.json'));
    }

    public function testUnsuccessfulContractSave()
    {
        vfsStream::setup('home');
        $this->setExpectedException(\RuntimeException::class);

        $this->getJuggler()->saveContract(
            new HttpImposter(),
            vfsStream::url('home/foo/bar.json')
        );
    }

    public function testContractSave()
    {
        vfsStream::setup('home');

        $this->getJuggler()->saveContract(new HttpImposter(), vfsStream::url('home/contract.json'));

        $this->assertSame(
            '{"protocol":"http","stubs":[]}',
            file_get_contents(vfsStream::url('home/contract.json'))
        );
    }

    /**
     * @covers \Meare\Juggler\Juggler::getImposter
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
            ->with('/imposters/4545?replayable=true&remove_proxies=true')
            ->willReturn($contract);

        $imposter = $this->getJuggler()->getImposter(4545, true, true);

        $this->assertEquals(4646, $imposter->getPort());
        $this->assertEquals('http', $imposter->getProtocol());
        $this->assertEquals('test imposter', $imposter->getName());
    }

    /**
     * @return Juggler
     */
    private function getJuggler()
    {
        return new Juggler('localhost', 2525, $this->httpClient);
    }
}
