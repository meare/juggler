<?php


use Meare\Juggler\Exception\Mountebank\InvalidInjectionException;
use Meare\Juggler\Exception\MountebankExceptionFactory;

class MountebankExceptionFactoryTest extends PHPUnit_Framework_TestCase
{
    public function testCreateInstanceFromMountebankResponse()
    {
        $factory = new MountebankExceptionFactory;
        $e = $factory->createInstanceFromMountebankResponse(<<<EOT
{
  "errors": [
    {
      "code": "invalid injection",
      "message": "invalid response injection",
      "source": "([object Object])(scope, injectState, logger, deferred.resolve);",
      "data": "Unexpected identifier"
    }
  ]
}
EOT
        );

        $this->assertInstanceOf(InvalidInjectionException::class, $e);
        $this->assertEquals('([object Object])(scope, injectState, logger, deferred.resolve);', $e->getSource());
        $this->assertEquals('Unexpected identifier', $e->getData());
    }

    public function testInvalidErrorCode()
    {
        $factory = new MountebankExceptionFactory;
        $this->expectException(\InvalidArgumentException::class);

        $factory->createInstance('invalid error code', '');
    }
}
