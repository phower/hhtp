<?php

namespace Phower\Http;

function fseek($handle, $offset, $whence = SEEK_SET)
{
    if (\PhowerTest\Http\StreamTest::$fseekReturns !== null) {
        return \PhowerTest\Http\StreamTest::$fseekReturns;
    }
    return \fseek($handle, $offset, $whence = SEEK_SET);
}

function fread($handle, $length)
{
    if (\PhowerTest\Http\StreamTest::$freadReturns !== null) {
        return \PhowerTest\Http\StreamTest::$freadReturns;
    }
    return \fread($handle, $length);
}

function ftell($handle)
{
    if (\PhowerTest\Http\StreamTest::$ftellReturns !== null) {
        return \PhowerTest\Http\StreamTest::$ftellReturns;
    }
    return \ftell($handle);
}

function fwrite($handle, $string, $length = null)
{
    if (\PhowerTest\Http\StreamTest::$fwriteReturns !== null) {
        return \PhowerTest\Http\StreamTest::$fwriteReturns;
    }
    $length = is_null($length) ? strlen($string) : $length;
    return \fwrite($handle, $string, $length);
}

function stream_get_contents($handle, $maxlength = -1, $offset = -1)
{
    if (\PhowerTest\Http\StreamTest::$streamGetContentsReturns !== null) {
        return \PhowerTest\Http\StreamTest::$streamGetContentsReturns;
    }
    return \stream_get_contents($handle, $maxlength, $offset);
}

function stream_get_meta_data($stream)
{
    if (\PhowerTest\Http\StreamTest::$streamGetMetaDataReturns !== null) {
        return \PhowerTest\Http\StreamTest::$streamGetMetaDataReturns;
    }
    return \stream_get_meta_data($stream);
}

namespace PhowerTest\Http;

class StreamTest extends \PHPUnit_Framework_TestCase
{

    public static $freadReturns = null;
    public static $fseekReturns = null;
    public static $ftellReturns = null;
    public static $fwriteReturns = null;
    public static $streamGetContentsReturns = null;
    public static $streamGetMetaDataReturns = null;

    protected function tearDown()
    {
        parent::tearDown();
        self::$freadReturns = null;
        self::$fseekReturns = null;
        self::$ftellReturns = null;
        self::$fwriteReturns = null;
        self::$streamGetContentsReturns = null;
        self::$streamGetMetaDataReturns = null;
    }

    public function testClassImplementsPsr7StreamInterface()
    {
        $stream = $this->getMockBuilder(\Phower\Http\Stream::class)
                ->disableOriginalConstructor()
                ->getMock();

        $this->assertInstanceOf(\Psr\Http\Message\StreamInterface::class, $stream);
    }

    public function testConstructAcceptsResource()
    {
        $resource = fopen('php://memory', 'wb+');
        $stream = new \Phower\Http\Stream($resource);
        $this->assertInstanceOf(\Psr\Http\Message\StreamInterface::class, $stream);
    }

    public function testConstructAcceptsStreamIdentifier()
    {
        $stream = new \Phower\Http\Stream('php://memory', 'wb+');
        $this->assertInstanceOf(\Psr\Http\Message\StreamInterface::class, $stream);
    }

    public function testConstructThrowsExceptionWhenStreamIdentifierCantBeOpen()
    {
        $this->setExpectedException(\Phower\Http\Exception\InvalidArgumentException::class);
        $stream = new \Phower\Http\Stream(__DIR__ . '/not_a_file', 'rb+');
    }

    public function testConstructThrowsExceptionWhenStreamIsNotAResourceOrAValidIdentifier()
    {
        $this->setExpectedException(\Phower\Http\Exception\InvalidArgumentException::class);
        $stream = new \Phower\Http\Stream(true);
    }

    public function testToStringReturnsContentOfResource()
    {
        $expected = file_get_contents(__FILE__);
        $resource = fopen(__FILE__, 'rb+');
        $stream = new \Phower\Http\Stream($resource);
        $this->assertEquals($expected, (string) $stream);
        $stream->detach();
        $this->assertEquals('', (string) $stream);
    }

    public function testToStringCatchesExceptionAndReturnsEmptyString()
    {
        $resource = fopen(__FILE__, 'rb+');
        $stream = new \Phower\Http\Stream($resource);
        self::$streamGetContentsReturns = false;
        $this->assertEquals('', (string) $stream);
    }

    public function testCloseCanCloseAnExistingResource()
    {
        $resource = fopen(__FILE__, 'rb+');
        $stream = new \Phower\Http\Stream($resource);
        $this->assertTrue(is_int($stream->getSize()));
        $stream->close();
        $this->assertNull($stream->getSize());
        $stream->close();
        $this->assertNull($stream->getSize());
    }

    public function testDetachSetsResourceToNullAndReturnsItWhenPresent()
    {
        $resource = fopen('php://memory', 'wb+');
        $stream = new \Phower\Http\Stream($resource);
        $this->assertSame($resource, $stream->detach());
        $this->assertNull($stream->detach());
    }

    public function testGetSizeReturnsLegthOfResourceOrNullIfNoResource()
    {
        $resource = fopen(__FILE__, 'rb+');
        $fstat = fstat($resource);
        $stream = new \Phower\Http\Stream($resource);
        $this->assertEquals($fstat['size'], $stream->getSize());

        $stream->detach();
        $this->assertNull($stream->getSize());
    }

    public function testTellReturnsPositionOfResourcePointer()
    {
        $resource = fopen(__FILE__, 'rb+');
        fseek($resource, 10);
        $stream = new \Phower\Http\Stream($resource);
        $this->assertEquals(10, $stream->tell());
    }

    public function testTellThrowsExceptionWhenResourceIsNotPresent()
    {
        $resource = fopen(__FILE__, 'rb+');
        $stream = new \Phower\Http\Stream($resource);
        $stream->detach();
        $this->setExpectedException(\Phower\Http\Exception\RuntimeException::class);
        $stream->tell();
    }

    public function testTellThrowsExceptionWhenCantReturnPointerPosition()
    {
        $resource = fopen(__FILE__, 'rb+');
        $stream = new \Phower\Http\Stream($resource);
        self::$ftellReturns = false;
        $this->setExpectedException(\Phower\Http\Exception\RuntimeException::class);
        $stream->tell();
    }

    public function testEofReturnsTrueWhenFilePointerReachesEndOfFile()
    {
        $resource = fopen(__FILE__, 'rb+');
        while (!feof($resource)) {
            fread($resource, 1024);
        }
        $stream = new \Phower\Http\Stream($resource);
        $this->assertTrue($stream->eof());
    }

    public function testEofReturnsTrueEvenWhenNoResourceIsPresent()
    {
        $resource = fopen(__FILE__, 'rb+');
        $stream = new \Phower\Http\Stream($resource);
        $stream->detach();
        $this->assertTrue($stream->eof());
    }

    public function testIsSeekableReturnsTrueWhenResourceIsSeekable()
    {
        $resource = fopen(__FILE__, 'rb+');
        $stream = new \Phower\Http\Stream($resource);
        $this->assertTrue($stream->isSeekable());
    }

    public function testIsSeekableReturnsFalseWhenNoResourceIsPresent()
    {
        $resource = fopen(__FILE__, 'rb+');
        $stream = new \Phower\Http\Stream($resource);
        $stream->detach();
        $this->assertFalse($stream->isSeekable());
    }

    public function testSeekCanMoveResourcePointerToAGivenPosition()
    {
        $resource = fopen(__FILE__, 'rb+');
        $stream = new \Phower\Http\Stream($resource);
        $this->assertTrue($stream->seek(10));
        $this->assertEquals(10, $stream->tell());
    }

    public function testSeekThrowsExceptionWhenResourceIsNotPresent()
    {
        $resource = fopen(__FILE__, 'rb+');
        $stream = new \Phower\Http\Stream($resource);
        $stream->detach();
        $this->setExpectedException(\Phower\Http\Exception\RuntimeException::class);
        $stream->seek(10);
    }

    public function testSeekThrowsExceptionWhenResourceIsNotSeekabla()
    {
        $resource = fopen(__FILE__, 'rb+');
        $stream = new \Phower\Http\Stream($resource);
        self::$streamGetMetaDataReturns = ['seekable' => false];
        $this->setExpectedException(\Phower\Http\Exception\RuntimeException::class);
        $stream->seek(10);
    }

    public function testSeekThrowsExceptionWhenSeekingReturnsError()
    {
        $resource = fopen(__FILE__, 'rb+');
        $stream = new \Phower\Http\Stream($resource);
        self::$fseekReturns = 1;
        $this->setExpectedException(\Phower\Http\Exception\RuntimeException::class);
        $stream->seek(10);
    }

    public function testRewindMovesPointerToTheBeginingOfResource()
    {
        $resource = fopen(__FILE__, 'rb+');
        $stream = new \Phower\Http\Stream($resource);
        $stream->seek(10);
        $this->assertEquals(10, $stream->tell());
        $stream->rewind();
        $this->assertEquals(0, $stream->tell());
    }

    public function testIsWritableReturnsWheterOrNotAResourceIsWritable()
    {
        $file = tempnam(sys_get_temp_dir(), 'phower-');
        $resource = fopen($file, 'wb+');
        $stream = new \Phower\Http\Stream($resource);
        $this->assertTrue($stream->isWritable());

        $stream->detach();
        $this->assertFalse($stream->isWritable());
    }

    public function testWriteCanWriteToTheResource()
    {
        $file = tempnam(sys_get_temp_dir(), 'phower-');
        $resource = fopen($file, 'wb+');
        $stream = new \Phower\Http\Stream($resource);
        $this->assertEquals(6, $stream->write('Phower'));
    }

    public function testWriteThrowsExceptionWhenResourceIsNotPresent()
    {
        $file = tempnam(sys_get_temp_dir(), 'phower-');
        $resource = fopen($file, 'wb+');
        $stream = new \Phower\Http\Stream($resource);
        $stream->detach();
        $this->setExpectedException(\Phower\Http\Exception\RuntimeException::class);
        $stream->write('Phower');
    }

    public function testWriteThrowsExceptionWhenItFailsToWriteResource()
    {
        $file = tempnam(sys_get_temp_dir(), 'phower-');
        $resource = fopen($file, 'wb+');
        $stream = new \Phower\Http\Stream($resource);
        self::$fwriteReturns = false;
        $this->setExpectedException(\Phower\Http\Exception\RuntimeException::class);
        $stream->write('Phower');
    }

    public function testIsReadableReturnsWheterOrNotAResourceIsReadable()
    {
        $resource = fopen(__FILE__, 'rb+');
        $stream = new \Phower\Http\Stream($resource);
        $this->assertTrue($stream->isReadable());

        $stream->detach();
        $this->assertFalse($stream->isReadable());
    }

    public function testReadCanReadFromTheResource()
    {
        $resource = fopen(__FILE__, 'rb+');
        $expected = fread($resource, 10);
        rewind($resource);
        $stream = new \Phower\Http\Stream($resource);
        $this->assertEquals($expected, $stream->read(10));
    }

    public function testReadThrowsExceptionWhenResourceIsNotPresent()
    {
        $resource = fopen(__FILE__, 'rb+');
        $stream = new \Phower\Http\Stream($resource);
        $stream->detach();
        $this->setExpectedException(\Phower\Http\Exception\RuntimeException::class);
        $stream->read(10);
    }

    public function testReadThrowsExceptionWhenResourceIsNotReadable()
    {
        $file = tempnam(sys_get_temp_dir(), 'phower-');
        $resource = fopen($file, 'wb');
        $stream = new \Phower\Http\Stream($resource);
        $this->setExpectedException(\Phower\Http\Exception\RuntimeException::class);
        $stream->read(10);
    }

    public function testReadThrowsExceptionWhenReadingReturnsError()
    {
        $file = tempnam(sys_get_temp_dir(), 'phower-');
        $resource = fopen($file, 'rb+');
        $stream = new \Phower\Http\Stream($resource);
        self::$freadReturns = false;
        $this->setExpectedException(\Phower\Http\Exception\RuntimeException::class);
        $stream->read(10);
    }

    public function testGetContentsReadsContentsFromCurrentResourcePointerPosition()
    {
        $expected = file_get_contents(__FILE__);
        $resource = fopen(__FILE__, 'rb+');
        $stream = new \Phower\Http\Stream($resource);
        $this->assertEquals($expected, $stream->getContents());

        $stream->detach();
        $this->assertEquals('', $stream->getContents());
    }

    public function testGetContentsThrowsExceptionWhetItCantReadFromResource()
    {
        $resource = fopen(__FILE__, 'rb+');
        $stream = new \Phower\Http\Stream($resource);
        self::$streamGetContentsReturns = false;
        $this->setExpectedException(\Phower\Http\Exception\RuntimeException::class);
        $stream->getContents();
    }

    public function testGetMetadataCanReadMetadataAboutTheResource()
    {
        $resource = fopen(__FILE__, 'rb+');
        $expected = stream_get_meta_data($resource);
        $stream = new \Phower\Http\Stream($resource);
        $this->assertEquals($expected, $stream->getMetadata());
        $this->assertEquals(__FILE__, $stream->getMetadata('uri'));
        $this->assertNull($stream->getMetadata('invalid key'));
    }

}
