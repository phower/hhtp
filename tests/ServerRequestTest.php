<?php

namespace PhowerTest\Http;

class ServerRequestTest extends \PHPUnit_Framework_TestCase
{

    public function testClassImplementsPsr7ServerRequestInterface()
    {
        $message = $this->getMockBuilder(\Phower\Http\ServerRequest::class)
                ->disableOriginalConstructor()
                ->getMock();

        $this->assertInstanceOf(\Psr\Http\Message\ServerRequestInterface::class, $message);
    }

}
