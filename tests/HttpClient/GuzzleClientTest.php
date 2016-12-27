<?php


use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Meare\Juggler\Exception\Mountebank\MountebankException;
use Meare\Juggler\Exception\MountebankExceptionFactory;
use Meare\Juggler\HttpClient\GuzzleClient;

class GuzzleClientTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject|MountebankExceptionFactory
     */
    private $mbExceptionFactory;

    /**
     * @var string
     */
    private $host = 'http://mountebank';

    /**
     * @var string
     */
    private $defaultResponseBody = '{}';

    public function setUp()
    {
        $this->mbExceptionFactory = $this->getMockBuilder(MountebankExceptionFactory::class)
            ->getMock();
    }

    public function testHostNosSet()
    {
        $client = $this->getGuzzleJugglerClient(false);

        $this->expectException(\LogicException::class);

        $client->get('/');
    }

    /**
     * @param bool $set_host
     * @return GuzzleClient
     */
    private function getGuzzleJugglerClient($set_host = true)
    {
        if (null === $this->client) {
            $this->setGuzzleMock([]);
        }
        $client = new GuzzleClient(
            $this->client,
            $this->mbExceptionFactory
        );
        if ($set_host) {
            $client->setHost($this->host);
        }

        return $client;
    }

    /**
     * @param array|null $queue
     * @param array      $container
     */
    private function setGuzzleMock($queue = null, array &$container = [])
    {
        if (null === $queue) {
            $queue = [new Response(200, [], $this->defaultResponseBody)];
        }
        $mock = new MockHandler($queue);
        $handler = HandlerStack::create($mock);
        $handler->push(Middleware::history($container));
        $this->client = new Client(['handler' => $handler]);
    }

    public function testClientExceptionIsTransformedToMountebankException()
    {
        // Imitate 500 response and throw ClientException
        $error_response = '{"body": "a"}';
        $this->setGuzzleMock([
            new ClientException(
                'random client exception',
                new \GuzzleHttp\Psr7\Request('GET', '/'),
                new Response(500, [], $error_response)
            ),
        ]);

        // Expect class to call MountebankExceptionFactory to transform ClientException to MountebankException
        $this->mbExceptionFactory->method('createInstanceFromMountebankResponse')
            ->willReturn(new MountebankException('dummy error'));
        $this->mbExceptionFactory->expects($this->once())
            ->method('createInstanceFromMountebankResponse')
            ->with($error_response);

        // Build object
        $client = $this->getGuzzleJugglerClient();

        // Make sure exception is not caught
        $this->expectException(MountebankException::class);

        $client->request('GET', '/');
    }

    /**
     * Tests request to mountebank;
     * Expects request to be sent to valid host with valid body.
     */
    public function testRequest()
    {
        $container = [];
        $this->setGuzzleMock([new Response(200, [], '{}')], $container);
        $client = $this->getGuzzleJugglerClient();

        $client->request('POST', '/imposters', 'imposter contract');

        $this->assertSame(1, sizeof($container), 'GuzzleClient should have performed exactly one request');
        /** @var \GuzzleHttp\Psr7\Request $request */
        $request = $container[0]['request'];
        $this->assertEquals('http://mountebank/imposters', (string)$request->getUri());
        $this->assertEquals('imposter contract', (string)$request->getBody());
    }

    public function testRequestWithoutBody()
    {
        $container = [];
        $this->setGuzzleMock([new Response(200, [], '{}')], $container);
        $client = $this->getGuzzleJugglerClient();

        $client->request('GET', '/');

        $this->assertSame(1, sizeof($container), 'GuzzleClient should have performed exactly one request');
        /** @var \GuzzleHttp\Psr7\Request $request */
        $request = $container[0]['request'];
        $this->assertEmpty((string)$request->getBody());
    }

    public function testGet()
    {
        $this->setGuzzleMock();

        $this->assertSame($this->defaultResponseBody, $this->getGuzzleJugglerClient()->get('/'));
    }

    public function testPost()
    {
        $this->setGuzzleMock();

        $this->assertSame($this->defaultResponseBody, $this->getGuzzleJugglerClient()->post('/', '{}'));
    }

    public function testPut()
    {
        $this->setGuzzleMock();

        $this->assertSame($this->defaultResponseBody, $this->getGuzzleJugglerClient()->put('/', '{}'));
    }

    public function testDelete()
    {
        $this->setGuzzleMock();

        $this->assertSame($this->defaultResponseBody, $this->getGuzzleJugglerClient()->delete('/'));
    }
}
