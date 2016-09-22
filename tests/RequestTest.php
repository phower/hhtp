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
 * Request class test case.
 *
 * @author Pedro Ferreira <pedro@phower.com>
 */
class RequestTest extends \PHPUnit_Framework_TestCase
{

    public function testClassImplementsPsr7RequestInterface()
    {
        $message = $this->getMockBuilder(\Phower\Http\Request::class)
                ->disableOriginalConstructor()
                ->getMock();

        $this->assertInstanceOf(\Psr\Http\Message\RequestInterface::class, $message);
    }

    /**
     * @dataProvider constructProvider
     */
    public function testConstructCanInstantiateWithArguments($uri, $method, $body = 'php://memory', $headers = [])
    {
        $request = new \Phower\Http\Request($uri, $method, $body, $headers);

        $this->assertAttributeInstanceOf(\Psr\Http\Message\UriInterface::class, 'uri', $request);
        $this->assertAttributeEquals($method, 'method', $request);
        $this->assertAttributeInstanceOf(\Psr\Http\Message\StreamInterface::class, 'stream', $request);
        $this->assertAttributeInternalType('array', 'headers', $request);
        $this->assertAttributeCount(count($headers), 'headers', $request);
        $this->assertAttributeInternalType('array', 'headerNames', $request);
        $this->assertAttributeCount(count($headers), 'headerNames', $request);
    }

    public function constructProvider()
    {
        $uri = $this->getMockBuilder(\Psr\Http\Message\UriInterface::class)->getMock();
        $body = $this->getMockBuilder(\Psr\Http\Message\StreamInterface::class)->getMock();

        return [
            ['/', 'GET'],
            [$uri, 'GET'],
            ['/', 'POST', 'php://memory'],
            ['/', 'POST', $body],
            ['/', 'POST', 'php://memory', ['Host' => 'example.org']],
        ];
    }

    public function testGetRequestTargetReturnsRequestTargetWhenItIsSpecified()
    {
        $expected = '/some/request/target';
        $request = new \Phower\Http\Request('/some/path', 'GET');

        $this->assertEquals($expected, $request->withRequestTarget($expected)->getRequestTarget());
    }

    public function testGetRequestTargetReturnsUriPathRequestTargetWhenRequestTargetIsNotSpecified()
    {
        $expected = '/some/path?key=value';
        $request = new \Phower\Http\Request($expected, 'GET');

        $this->assertEquals($expected, $request->getRequestTarget());
    }

    public function testWithRequestTargetRaisesExceptionWhenRequestTargetContainsWhiteSpaces()
    {
        $request = new \Phower\Http\Request('/', 'GET');

        $this->setExpectedException(\Phower\Http\Exception\InvalidArgumentException::class);
        $request->withRequestTarget('target with spaces');
    }

    public function testGetMethodReturnsMethod()
    {
        $request = new \Phower\Http\Request('/', 'get');
        $this->assertEquals('GET', $request->getMethod());
    }

    public function testWithMethodReturnsClonedInstanceWithGivenMethod()
    {
        $request = new \Phower\Http\Request('/', 'GET');
        $clone = $request->withMethod('POST');

        $this->assertInstanceOf(\Phower\Http\Request::class, $clone);
        $this->assertNotSame($clone, $request);
        $this->assertEquals('POST', $clone->getMethod());
    }

    public function testGetUriReturnsUri()
    {
        $uri = new \Phower\Http\Uri();
        $request = new \Phower\Http\Request($uri, 'get');
        $this->assertSame($uri, $request->getUri());
    }

    public function testWithUriReturnsClonedInstanceWithGivenUri()
    {
        $uri = new \Phower\Http\Uri('/some/path');
        $request = new \Phower\Http\Request('/', 'GET');
        $clone = $request->withUri($uri);

        $this->assertInstanceOf(\Phower\Http\Request::class, $clone);
        $this->assertNotSame($clone, $request);
        $this->assertEquals($uri, $clone->getUri());
    }

    public function testWithUriCanUpdateHostHeadersFromGivenUri()
    {
        $host = 'example.org';
        $port = 666;
        $uri = new \Phower\Http\Uri('/some/path');
        $request = new \Phower\Http\Request('/', 'GET');
        $this->assertCount(0, $request->getHeaders());

        $clone = $request->withUri($uri->withHost($host)->withPort($port));

        $this->assertInstanceOf(\Phower\Http\Request::class, $clone);
        $this->assertNotSame($clone, $request);
        $this->assertCount(1, $clone->getHeaders());
        $this->assertEquals($uri->withHost($host)->withPort($port), $clone->getUri());
    }

    public function testValidateUriRaisesExceptionWhenUriIsNotAnInstanceOfUriInterfaceOrAString()
    {
        $this->setExpectedException(\Phower\Http\Exception\InvalidArgumentException::class);
        $request = new \Phower\Http\Request(123, 'GET');
    }

    public function testValidateMethodRaisesExceptionWhenMethodIsNotAString()
    {
        $this->setExpectedException(\Phower\Http\Exception\InvalidArgumentException::class);
        $request = new \Phower\Http\Request('/', 123);
    }

    public function testValidateMethodRaisesExceptionWhenMethodIsNotAValidHttpMethod()
    {
        $this->setExpectedException(\Phower\Http\Exception\InvalidArgumentException::class);
        $request = new \Phower\Http\Request('/', 'TEST');
    }
}
