<?php

/**
 * Phower Http
 *
 * @version 0.0.0
 * @link https://github.com/phower/http Public Git repository
 * @copyright (c) 2015-2016, Pedro Ferreira <https://phower.com>
 * @license https://opensource.org/licenses/MIT MIT
 */

namespace Phower\Http;

/**
 * Hacked version of fopen function, used only for tests.
 *
 * @author Pedro Ferreira <pedro@phower.com>
 */
function fopen($filename, $mode, $use_include_path = false, $context = null)
{
    if (\PhowerTest\Http\UploadedFileTest::$fopenReturns !== null) {
        return \PhowerTest\Http\UploadedFileTest::$fopenReturns;
    }
    if ($context === null) {
        return \fopen($filename, $mode, $use_include_path);
    }
    return \fopen($filename, $mode, $use_include_path, $context);
}

namespace PhowerTest\Http;

/**
 * Uploaded file class test case.
 *
 * @author Pedro Ferreira <pedro@phower.com>
 */
class UploadedFileTest extends \PHPUnit_Framework_TestCase
{

    public static $fopenReturns = null;

    protected function tearDown()
    {
        parent::tearDown();
        self::$fopenReturns = null;
    }

    public function testClassImplementsPsr7UploadedFileInterface()
    {
        $message = $this->getMockBuilder(\Phower\Http\UploadedFile::class)
                ->disableOriginalConstructor()
                ->getMock();

        $this->assertInstanceOf(\Psr\Http\Message\UploadedFileInterface::class, $message);
    }

    public function testConstructCanInstantiateWithSourceFileName()
    {
        $source = tempnam(sys_get_temp_dir(), 'phower-');
        file_put_contents($source, 'Phower');
        $size = filesize($source);
        $error = 0;
        $name = 'client_filename.txt';
        $type = 'text/plain';

        $upload = new \Phower\Http\UploadedFile($source, $size, $error, $name, $type);
        $this->assertInstanceOf(\Phower\Http\UploadedFile::class, $upload);
        $this->assertEquals($size, $upload->getSize());
        $this->assertEquals($error, $upload->getError());
        $this->assertEquals($name, $upload->getClientFilename());
        $this->assertEquals($type, $upload->getClientMediaType());
    }

    public function testConstructCanInstantiateWithResource()
    {
        $tmpname = tempnam(sys_get_temp_dir(), 'phower-');
        file_put_contents($tmpname, 'Phower');
        $size = filesize($tmpname);
        $error = 0;
        $name = 'client_filename.txt';
        $type = 'text/plain';

        $source = fopen($tmpname, 'rb+');
        $upload = new \Phower\Http\UploadedFile($source, $size, $error, $name, $type);

        $this->assertInstanceOf(\Phower\Http\UploadedFile::class, $upload);
        $this->assertInstanceOf(\Psr\Http\Message\StreamInterface::class, $upload->getStream());
        $this->assertEquals($size, $upload->getSize());
        $this->assertEquals($error, $upload->getError());
        $this->assertEquals($name, $upload->getClientFilename());
        $this->assertEquals($type, $upload->getClientMediaType());
    }

    public function testConstructCanInstantiateWithInstanceOfStreamInterface()
    {
        $tmpname = tempnam(sys_get_temp_dir(), 'phower-');
        file_put_contents($tmpname, 'Phower');
        $size = filesize($tmpname);
        $error = 0;
        $name = 'client_filename.txt';
        $type = 'text/plain';

        $source = new \Phower\Http\Stream($tmpname);
        $upload = new \Phower\Http\UploadedFile($source, $size, $error, $name, $type);

        $this->assertInstanceOf(\Phower\Http\UploadedFile::class, $upload);
        $this->assertInstanceOf(\Psr\Http\Message\StreamInterface::class, $upload->getStream());
        $this->assertEquals($size, $upload->getSize());
        $this->assertEquals($error, $upload->getError());
        $this->assertEquals($name, $upload->getClientFilename());
        $this->assertEquals($type, $upload->getClientMediaType());
    }

    public function testConstructThrowsExceptionWhenSourceIsNotValid()
    {
        $this->setExpectedException(\Phower\Http\Exception\InvalidArgumentException::class);
        $upload = new \Phower\Http\UploadedFile(0, 0, 0);
    }

    public function testConstructThrowsExceptionWhenSizeIsNotInteger()
    {
        $this->setExpectedException(\Phower\Http\Exception\InvalidArgumentException::class);
        $upload = new \Phower\Http\UploadedFile('filename', false, 0);
    }

    public function testConstructThrowsExceptionWhenErrorIsNotInteger()
    {
        $this->setExpectedException(\Phower\Http\Exception\InvalidArgumentException::class);
        $upload = new \Phower\Http\UploadedFile('filename', 0, false);
    }

    public function testConstructThrowsExceptionWhenErrorIsLessThanZero()
    {
        $this->setExpectedException(\Phower\Http\Exception\InvalidArgumentException::class);
        $upload = new \Phower\Http\UploadedFile('filename', 0, -1);
    }

    public function testConstructThrowsExceptionWhenErrorIsGreaterThanEight()
    {
        $this->setExpectedException(\Phower\Http\Exception\InvalidArgumentException::class);
        $upload = new \Phower\Http\UploadedFile('filename', 0, 10);
    }

    public function testConstructThrowsExceptionWhenProvidedNameIsNotAString()
    {
        $this->setExpectedException(\Phower\Http\Exception\InvalidArgumentException::class);
        $upload = new \Phower\Http\UploadedFile('filename', 0, 0, 0);
    }

    public function testConstructThrowsExceptionWhenProvidedTypeIsNotAString()
    {
        $this->setExpectedException(\Phower\Http\Exception\InvalidArgumentException::class);
        $upload = new \Phower\Http\UploadedFile('filename', 0, 0, null, false);
    }

    public function testGetStreamCreatesNewStreamWhenFileNameIsProvided()
    {
        $source = tempnam(sys_get_temp_dir(), 'phower-');
        $upload = new \Phower\Http\UploadedFile($source, 0, 0);
        $this->assertInstanceOf(\Phower\Http\Stream::class, $upload->getStream());
    }

    public function testMoveToThrowsExceptionWhenTragetPathIsNotString()
    {
        $source = tempnam(sys_get_temp_dir(), 'phower-');
        $upload = new \Phower\Http\UploadedFile($source, 0, 0);
        $this->setExpectedException(\Phower\Http\Exception\InvalidArgumentException::class);
        $upload->moveTo(0);
    }

    public function testMoveToThrowsExceptionWhenTragetPathIsAnEmptyString()
    {
        $source = tempnam(sys_get_temp_dir(), 'phower-');
        $upload = new \Phower\Http\UploadedFile($source, 0, 0);
        $this->setExpectedException(\Phower\Http\Exception\InvalidArgumentException::class);
        $upload->moveTo('');
    }

    public function testMoveToMovesUploadFileToTargetPath()
    {
        $source = tempnam(sys_get_temp_dir(), 'phower-');
        $target = "$source-moved";
        $this->assertFileNotExists($target);
        $upload = new \Phower\Http\UploadedFile($source, 0, 0);
        $upload->moveTo($target);
        $this->assertFileExists($target);

        $source = tempnam(sys_get_temp_dir(), 'phower-');
        $target = "$source-moved";
        $this->assertFileNotExists($target);
        $upload = new \Phower\Http\UploadedFile(fopen($source, 'rb'), 0, 0);
        $upload->moveTo($target);
        $this->assertFileExists($target);
    }

    public function testMoveToThrowsExceptionWhenTryToMoveSameFileMoreThanOnce()
    {
        $source = tempnam(sys_get_temp_dir(), 'phower-');
        $target = "$source-moved";
        $upload = new \Phower\Http\UploadedFile($source, 0, 0);
        $upload->moveTo($target);
        $this->setExpectedException(\Phower\Http\Exception\RuntimeException::class);
        $upload->moveTo($target);
    }

    public function testWriteFileThrowsExceptionWhenTargetPathIsNotWritable()
    {
        $source = tempnam(sys_get_temp_dir(), 'phower-');
        $target = "$source-moved";
        $upload = new \Phower\Http\UploadedFile($source, 0, 0);
        self::$fopenReturns = false;
        $this->setExpectedException(\Phower\Http\Exception\RuntimeException::class);
        $upload->moveTo($target);
    }

    public function testGetStreamThrowsExceptionWhenCaleedAfterMoveTo()
    {
        $source = tempnam(sys_get_temp_dir(), 'phower-');
        $target = "$source-moved";
        $upload = new \Phower\Http\UploadedFile($source, 0, 0);
        $upload->moveTo($target);
        $this->setExpectedException(\Phower\Http\Exception\RuntimeException::class);
        $upload->getStream();
    }
}
