<?php

namespace PhowerTest\Http;

class UriTest extends \PHPUnit_Framework_TestCase
{

    public function testClassImplementsPsr7UriInterface()
    {
        $message = $this->getMockBuilder(\Phower\Http\Uri::class)
                ->disableOriginalConstructor()
                ->getMock();

        $this->assertInstanceOf(\Psr\Http\Message\UriInterface::class, $message);
    }

}
