<?php

namespace PhowerTest\Http;

class StreamTest extends \PHPUnit_Framework_TestCase
{

    public function testClassImplementsPsr7StreamInterface()
    {
        $message = $this->getMockBuilder(\Phower\Http\Stream::class)
                ->disableOriginalConstructor()
                ->getMock();

        $this->assertInstanceOf(\Psr\Http\Message\StreamInterface::class, $message);
    }

}
