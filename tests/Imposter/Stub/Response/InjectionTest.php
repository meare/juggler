<?php

namespace Meare\Juggler\Test\Imposter\Stub\Response;


use Meare\Juggler\Imposter\Stub\Injection;
use PHPUnit_Framework_TestCase;

class InjectionTest extends PHPUnit_Framework_TestCase
{
    public function testStaticFactoryMethod()
    {
        $js = 'function(){}';
        $inject = Injection::createFromContract($js);
        $this->assertSame($js, $inject->getJs());
    }

    public function testCompile()
    {
        $inject = new Injection('function(){}');
        $this->assertSame(['inject' => 'function(){}'], $inject->jsonSerialize());
    }

    public function testSetJs()
    {
        $inject = new Injection('function(){}');
        $new_js = 'function(){alert("ururu");}';

        $inject->setJs($new_js);

        $this->assertSame($new_js, $inject->getJs());
    }
}
