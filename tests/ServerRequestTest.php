<?php

/**
 * Phower Http
 *
 * @version 0.0.0
 * @link https://github.com/phower/http Public Git repository
 * @copyright (c) 2015-2016, Pedro Ferreira <https://phower.com>
 * @license https://opensource.org/licenses/MIT MIT
 */

namespace PhowerTest\Http;

/**
 * Server request class test case.
 *
 * @author Pedro Ferreira <pedro@phower.com>
 */
class ServerRequestTest extends \PHPUnit_Framework_TestCase
{

    public function testClassImplementsPsr7ServerRequestInterface()
    {
        $message = $this->getMockBuilder(\Phower\Http\ServerRequest::class)
                ->disableOriginalConstructor()
                ->getMock();

        $this->assertInstanceOf(\Psr\Http\Message\ServerRequestInterface::class, $message);
    }

    /**
     * @dataProvider constructProvider
     */
    public function testConstructCanCreateNewInstance($server = [], $files = [], $uri = '/', $method = 'GET', $body = 'php://input', $headers = [])
    {
        $request = new \Phower\Http\ServerRequest($server, $files, $uri, $method, $body, $headers);

        $this->assertAttributeEquals($server, 'serverParams', $request);
        $this->assertAttributeEquals($files, 'uploadedFiles', $request);
    }

    public function constructProvider()
    {
        $server = ['foo' => 'bar', 'baz' => 'bat'];

        $file1 = $this->createMock(\Psr\Http\Message\UploadedFileInterface::class);
        $file2 = $this->createMock(\Psr\Http\Message\UploadedFileInterface::class);
        $file3 = $this->createMock(\Psr\Http\Message\UploadedFileInterface::class);

        return [
            [],
            [$server],
            [$server, [$file1]],
            [$server, [$file1, $file2]],
            [$server, [$file1, [$file2, $file3]]],
        ];
    }

    public function testGetServerParamsReturnsServerParams()
    {
        $server = ['foo' => 'bar', 'baz' => 'bat'];
        $request = new \Phower\Http\ServerRequest($server);
        $this->assertEquals($server, $request->getServerParams());
    }

    public function testWithCookieParamsReturnsAClonnedInstanceWithProvidedCookies()
    {
        $request = new \Phower\Http\ServerRequest();
        $this->assertEmpty($request->getCookieParams());

        $cookies = ['key' => 'value'];
        $clone = $request->withCookieParams($cookies);

        $this->assertNotSame($clone, $request);
        $this->assertEquals($cookies, $clone->getCookieParams());
    }

    public function testWithQueryParamsReturnsAClonnedInstanceWithProvidedQuery()
    {
        $request = new \Phower\Http\ServerRequest();
        $this->assertEmpty($request->getQueryParams());

        $query = ['key' => 'value'];
        $clone = $request->withQueryParams($query);

        $this->assertNotSame($clone, $request);
        $this->assertEquals($query, $clone->getQueryParams());
    }

    public function testWithUploadedFilesReturnsAClonnedInstanceWithProvidedUploadedFiles()
    {
        $request = new \Phower\Http\ServerRequest();
        $this->assertEmpty($request->getUploadedFiles());

        $files = [
            $this->createMock(\Psr\Http\Message\UploadedFileInterface::class),
            $this->createMock(\Psr\Http\Message\UploadedFileInterface::class),
        ];
        $clone = $request->withUploadedFiles($files);

        $this->assertNotSame($clone, $request);
        $this->assertEquals($files, $clone->getUploadedFiles());
    }

    public function testWithParsedBodyReturnsAClonnedInstanceWithProvidedParsedBody()
    {
        $request = new \Phower\Http\ServerRequest();
        $this->assertEmpty($request->getParsedBody());

        $body = ['foo' => 'bar', 'baz' => 'bat'];
        $clone = $request->withParsedBody($body);

        $this->assertNotSame($clone, $request);
        $this->assertEquals($body, $clone->getParsedBody());
    }

    public function testGetAttributeReturnsDefaultValueWhenNameIsNotSet()
    {
        $request = new \Phower\Http\ServerRequest();
        $this->assertNull($request->getAttribute('not_set'));

        $default = 123;
        $this->assertEquals($default, $request->getAttribute('not_set', $default));
    }

    public function testWithAttributeReturnsAClonnedInstanceWithProvidedAttribute()
    {
        $request = new \Phower\Http\ServerRequest();
        $this->assertEmpty($request->getAttributes());

        $name = 'bar';
        $value = 'bar';
        $clone = $request->withAttribute($name, $value);

        $this->assertNotSame($clone, $request);
        $this->assertEquals($value, $clone->getAttribute($name));

        return $clone;
    }

    /**
     * @depends testWithAttributeReturnsAClonnedInstanceWithProvidedAttribute
     * @param \Phower\Http\ServerRequest $request
     */
    public function testWithoutAttributeReturnsAClonnedInstanceWithoutReferredAttribute(\Phower\Http\ServerRequest $request)
    {
        $name = 'bar';
        $clone = $request->withoutAttribute($name);

        $this->assertNotSame($clone, $request);
        $this->assertNull($clone->getAttribute($name));

        $name = 'foo';
        $clone = $request->withoutAttribute($name);

        $this->assertNotSame($clone, $request);
        $this->assertNull($clone->getAttribute($name));
    }

    public function testValidateUploadedFilesRaisesExceptionWhenFileIsNotInstanceOfUploadedFile()
    {
        $request = new \Phower\Http\ServerRequest();
        $this->setExpectedException(\Phower\Http\Exception\InvalidArgumentException::class);
        $request->withUploadedFiles(['not an uploaded file']);
    }
}
