<?php

namespace PhowerTest\Http;

class ResponseTest extends \PHPUnit_Framework_TestCase
{

    public function testClassImplementsPsr7ResponseInterface()
    {
        $message = $this->getMockBuilder(\Phower\Http\Response::class)
                ->disableOriginalConstructor()
                ->getMock();

        $this->assertInstanceOf(\Psr\Http\Message\ResponseInterface::class, $message);
    }
}
