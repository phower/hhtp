<?php

namespace PhowerTest\Http;

class MessageTest extends \PHPUnit_Framework_TestCase
{

    public function testClassImplementsPsr7MessageInterface()
    {
        $message = $this->getMockBuilder(\Phower\Http\Message::class)
                ->disableOriginalConstructor()
                ->getMock();

        $this->assertInstanceOf(\Psr\Http\Message\MessageInterface::class, $message);
    }
}
