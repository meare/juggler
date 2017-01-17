<?php

namespace Meare\Juggler\Test\Imposter\Builder;


use Meare\Juggler\Imposter\Builder\HttpImposterBuilder;
use Meare\Juggler\Imposter\Stub\Stub;
use Meare\Juggler\Imposter\Stub\StubBuilder;
use PHPUnit_Framework_TestCase;

class HttpImposterBuilderTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var StubBuilder
     */
    private $stubBuilder;

    public function setUp()
    {
        $this->stubBuilder = $this->getMockBuilder(StubBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testImposterWithoutStubs()
    {
        $json = <<<'EOT'
        {
            "port": 4545,
            "protocol": "http",
            "name": "ServiceStub"
        }
EOT;
        $imposterBuilder = $this->getHttpImposterBuilder();
        $imposter = $imposterBuilder->build(\GuzzleHttp\json_decode($json, true));
        $this->assertSame(4545, $imposter->getPort());
        $this->assertSame('http', $imposter->getProtocol());
        $this->assertSame('ServiceStub', $imposter->getName());
    }

    private function getHttpImposterBuilder()
    {
        return new HttpImposterBuilder($this->stubBuilder);
    }

    public function testImposterWithStubs()
    {
        $json = <<<'EOT'
        {
          "port": 4545,
          "protocol": "http",
          "name": "imposter contract service",
          "stubs": [
            {
              "responses": [
                {
                  "proxy": {
                    "to": "https://www.somesite.com:3000"
                  }
                }
              ],
              "predicates": [
                {
                  "equals": {
                    "body": "value"
                  }
                }
              ]
            },
            {
              "responses": [
                {
                  "is": {
                    "statusCode": 201
                  }
                }
              ],
              "predicates": [
                {
                  "equals": {
                    "except": "^The "
                  }
                }
              ]
            }
          ]
        }
EOT;
        $this->stubBuilder = $this->getMockBuilder(StubBuilder::class)
            ->disableOriginalConstructor()
            ->setMethods(['build'])
            ->getMock();
        $this->stubBuilder->expects($this->exactly(2))
            ->method('build')
            ->withConsecutive([[
                'responses'  => [
                    ['proxy' => ['to' => 'https://www.somesite.com:3000']],
                ],
                'predicates' => [
                    ['equals' => ['body' => 'value']],
                ],
            ]], [[
                'responses'  => [
                    ['is' => ['statusCode' => 201]],
                ],
                'predicates' => [
                    ['equals' => ['except' => '^The ']],
                ],
            ]])
            ->willReturn(new Stub());

        /** @var StubBuilder $stubBuilder */
        $imposterBuilder = $this->getHttpImposterBuilder();
        $imposterBuilder->build(\GuzzleHttp\json_decode($json, true));
    }
}
