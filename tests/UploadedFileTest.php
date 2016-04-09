<?php

namespace PhowerTest\Http;

class UploadedFileTest extends \PHPUnit_Framework_TestCase
{

    public function testClassImplementsPsr7UploadedFileInterface()
    {
        $message = $this->getMockBuilder(\Phower\Http\UploadedFile::class)
                ->disableOriginalConstructor()
                ->getMock();

        $this->assertInstanceOf(\Psr\Http\Message\UploadedFileInterface::class, $message);
    }
}
