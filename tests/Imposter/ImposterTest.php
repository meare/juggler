<?php

namespace Meare\Juggler\Test\Imposter;


use Meare\Juggler\Imposter\HttpImposter;
use PHPUnit_Framework_TestCase;

class ImposterTest extends PHPUnit_Framework_TestCase
{
    public function testSetters()
    {
        $imposter = new HttpImposter(4545);
        $imposter->setName('test name');

        $this->assertTrue($imposter->hasName());
        $this->assertEquals('test name', $imposter->getName());
        $this->assertEquals(4545, $imposter->getPort());
        $this->assertEquals('http', $imposter->getProtocol());
    }

    public function testImposterWithoutPort()
    {
        $imposter = new HttpImposter;

        $this->assertNull($imposter->getPort());
    }

    public function testFindRequestsWithArray()
    {
        $requests = [
            [
                'method' => 'GET',
                'body'   => 'a',
            ],
            [
                'method' => 'PUT',
                'body'   => 'b',
            ],
        ];
        $imposter = new HttpImposter(null, $requests);

        $criteria = ['method' => 'GET'];
        $this->assertEquals([$requests[0]], $imposter->findRequests($criteria));
    }

    public function testFindRequestsWithCallback()
    {
        $requests = [
            [
                'body'   => 'a',
            ],
            [
                'body'   => 'b',
            ],
            [
                'body'   => 'c',
            ],
        ];
        $imposter = new HttpImposter(null, $requests);

        $callback = function($request) {
            return $request['body'] === 'a' || $request['body'] === 'c';
        };
        $this->assertEquals(
            [$requests[0], $requests[2]],
            $imposter->findRequests($callback)
        );
    }

    public function testHasRequestsByCriteria()
    {
        $requests = [
            [
                'method' => 'GET',
                'body'   => 'a',
            ],
            [
                'method' => 'PUT',
                'body'   => 'b',
            ],
        ];

        $imposter = new HttpImposter(null, $requests);

        $criteria = ['body' => 'a'];
        $this->assertTrue($imposter->hasRequestsByCriteria($criteria));
    }
}
