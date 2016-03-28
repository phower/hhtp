<?php

namespace PhowerTest\Http;

class RequestTest extends \PHPUnit_Framework_TestCase
{

    public function testClassImplementsPsr7RequestInterface()
    {
        $message = $this->getMockBuilder(\Phower\Http\Request::class)
                ->disableOriginalConstructor()
                ->getMock();

        $this->assertInstanceOf(\Psr\Http\Message\RequestInterface::class, $message);
    }

}
