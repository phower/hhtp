<?php

/**
 * Phower Http
 *
 * @version 1.0.0
 * @link https://github.com/phower/http Public Git repository
 * @copyright (c) 2015-2016, Pedro Ferreira <https://phower.com>
 * @license https://opensource.org/licenses/MIT MIT
 */

namespace PhowerTest\Http;

/**
 * Uri class test case.
 *
 * @author Pedro Ferreira <pedro@phower.com>
 */
class UriTest extends \PHPUnit_Framework_TestCase
{

    public function testClassImplementsPsr7UriInterface()
    {
        $message = $this->getMockBuilder(\Phower\Http\Uri::class)
                ->disableOriginalConstructor()
                ->getMock();

        $this->assertInstanceOf(\Psr\Http\Message\UriInterface::class, $message);
    }

    public function testConstructorAcceptsUriArgument()
    {
        $argument = 'http://user:pass@example.org:8008/some/path?a=1#tag';
        $uri = new \Phower\Http\Uri($argument);
        $this->assertEquals($argument, (string) $uri);
        $this->assertEquals('http', $uri->getScheme());
        $this->assertEquals('user:pass', $uri->getUserInfo());
        $this->assertEquals('example.org', $uri->getHost());
        $this->assertEquals(8008, $uri->getPort());
        $this->assertEquals('/some/path', $uri->getPath());
        $this->assertEquals('a=1', $uri->getQuery());
        $this->assertEquals('tag', $uri->getFragment());
    }

    public function testConstructorThrowsExceptionWhenArgumentIsNotString()
    {
        $this->setExpectedException(\Phower\Http\Exception\InvalidArgumentException::class);
        $uri = new \Phower\Http\Uri(123);
    }

    public function testConstructorThrowsExceptionWhenArgumentIsAValidUri()
    {
        $this->setExpectedException(\Phower\Http\Exception\InvalidArgumentException::class);
        $uri = new \Phower\Http\Uri('http:///phower.com');
    }

    public function testCloneSetsUriStringToNull()
    {
        $uri = new \Phower\Http\Uri('http://phower.com');

        $string = (string) $uri;
        $this->assertAttributeEquals($string, 'uriString', $uri);

        $clone = clone $uri;
        $this->assertAttributeEquals(null, 'uriString', $clone);
    }

    public function testToStringReusesUriString()
    {
        $uri = new \Phower\Http\Uri('http://phower.com');

        $this->assertAttributeEquals(null, 'uriString', $uri);
        $string = (string) $uri;
        $this->assertAttributeEquals($string, 'uriString', $uri);
        $new = (string) $uri;
        $this->assertEquals($string, $new);
    }

    public function testToStringEnsuresRootForwardSlashWhenPathIsMissingIt()
    {
        $uri = new \Phower\Http\Uri('http://phower.com');
        $new = $uri->withPath('path');
        $this->assertEquals('http://phower.com/path', (string) $new);
    }

    public function testGetAuthorityReturnsEmptyStringWhenThereIsNotHost()
    {
        $uri = new \Phower\Http\Uri('/');
        $this->assertEquals('', $uri->getAuthority());
    }

    public function testWithSchemeReturnsNewInstanceWithNewScheme()
    {
        $uri = new \Phower\Http\Uri('https://phower.com');

        $clone = $uri->withScheme('https');
        $this->assertNotSame($uri, $clone);
        $this->assertEquals('https', $clone->getScheme());
        $this->assertEquals('https://phower.com', (string) $clone);

        $new = $uri->withScheme('http');
        $this->assertNotSame($uri, $new);
        $this->assertEquals('http', $new->getScheme());
        $this->assertEquals('http://phower.com', (string) $new);
    }

    public function testWithUserInfoReturnsNewInstanceWithProvidedUser()
    {
        $uri = new \Phower\Http\Uri('https://user@phower.com');

        $clone = $uri->withUserInfo('user');
        $this->assertNotSame($uri, $clone);
        $this->assertEquals('user', $clone->getUserInfo());
        $this->assertEquals('https://user@phower.com', (string) $clone);

        $new = $uri->withUserInfo('pedro', 'secret');
        $this->assertNotSame($uri, $new);
        $this->assertEquals('pedro:secret', $new->getUserInfo());
        $this->assertEquals('https://pedro:secret@phower.com', (string) $new);
    }

    public function testWithHostReturnsNewInstanceWithProvidedHost()
    {
        $uri = new \Phower\Http\Uri('https://phower.com');

        $clone = $uri->withHost('phower.com');
        $this->assertNotSame($uri, $clone);
        $this->assertEquals('phower.com', $clone->getHost());
        $this->assertEquals('https://phower.com', (string) $clone);

        $new = $uri->withHost('php.net');
        $this->assertNotSame($uri, $new);
        $this->assertEquals('php.net', $new->getHost());
        $this->assertEquals('https://php.net', (string) $new);
    }

    public function testWithPortReturnsNewInstanceWithProvidedPort()
    {
        $uri = new \Phower\Http\Uri('https://phower.com:2222');

        $clone = $uri->withPort(2222);
        $this->assertNotSame($uri, $clone);
        $this->assertEquals(2222, $clone->getPort());
        $this->assertEquals('https://phower.com:2222', (string) $clone);

        $new = $uri->withPort(8080);
        $this->assertNotSame($uri, $new);
        $this->assertEquals(8080, $new->getPort());
        $this->assertEquals('https://phower.com:8080', (string) $new);
    }

    public function testWithPortThrowsExceptionWhenPortIsNotInteger()
    {
        $uri = new \Phower\Http\Uri('https://phower.com:2222');
        $this->setExpectedException(\Phower\Http\Exception\InvalidArgumentException::class);
        $new = $uri->withPort('port');
    }

    public function testWithPortThrowsExceptionWhenPortIsLessThen1()
    {
        $uri = new \Phower\Http\Uri('https://phower.com:2222');
        $this->setExpectedException(\Phower\Http\Exception\InvalidArgumentException::class);
        $new = $uri->withPort(0);
    }

    public function testWithPortThrowsExceptionWhenPortIsGreaterThen65535()
    {
        $uri = new \Phower\Http\Uri('https://phower.com:2222');
        $this->setExpectedException(\Phower\Http\Exception\InvalidArgumentException::class);
        $new = $uri->withPort(65536);
    }

    public function testWithPathReturnsNewInstanceWithProvidedPath()
    {
        $uri = new \Phower\Http\Uri('https://phower.com/some/path');

        $clone = $uri->withPath('/some/path');
        $this->assertNotSame($uri, $clone);
        $this->assertEquals('/some/path', $clone->getPath());
        $this->assertEquals('https://phower.com/some/path', (string) $clone);

        $new = $uri->withPath('/other/path');
        $this->assertNotSame($uri, $new);
        $this->assertEquals('/other/path', $new->getPath());
        $this->assertEquals('https://phower.com/other/path', (string) $new);
    }

    public function testWithPathThrowsExceptionWhenPathIsNotAString()
    {
        $uri = new \Phower\Http\Uri('https://phower.com:2222');
        $this->setExpectedException(\Phower\Http\Exception\InvalidArgumentException::class);
        $new = $uri->withPath(80);
    }

    public function testWithPathThrowsExceptionWhenPathContainsAQuestionMark()
    {
        $uri = new \Phower\Http\Uri('https://phower.com:2222');
        $this->setExpectedException(\Phower\Http\Exception\InvalidArgumentException::class);
        $new = $uri->withPath('/path/with/?');
    }

    public function testWithPathThrowsExceptionWhenPathContainsAnHash()
    {
        $uri = new \Phower\Http\Uri('https://phower.com:2222');
        $this->setExpectedException(\Phower\Http\Exception\InvalidArgumentException::class);
        $new = $uri->withPath('/path/with/#');
    }

    public function testWithQueryReturnsNewInstanceWithProvidedQuery()
    {
        $uri = new \Phower\Http\Uri('https://phower.com?a=1');

        $clone = $uri->withQuery('a=1');
        $this->assertNotSame($uri, $clone);
        $this->assertEquals('a=1', $clone->getQuery());
        $this->assertEquals('https://phower.com?a=1', (string) $clone);

        $new = $uri->withQuery('foo=bar');
        $this->assertNotSame($uri, $new);
        $this->assertEquals('foo=bar', $new->getQuery());
        $this->assertEquals('https://phower.com?foo=bar', (string) $new);
    }

    public function testWithQueryThrowsExceptionWhenQueryIsNotAString()
    {
        $uri = new \Phower\Http\Uri('https://phower.com');
        $this->setExpectedException(\Phower\Http\Exception\InvalidArgumentException::class);
        $new = $uri->withQuery(80);
    }

    public function testWithQueryThrowsExceptionWhenQueryContainsAnHash()
    {
        $uri = new \Phower\Http\Uri('https://phower.com:2222');
        $this->setExpectedException(\Phower\Http\Exception\InvalidArgumentException::class);
        $new = $uri->withQuery('?a=1#');
    }

    public function testWithFragmentReturnsNewInstanceWithProvidedFragment()
    {
        $uri = new \Phower\Http\Uri('https://phower.com#tag');

        $clone = $uri->withFragment('tag');
        $this->assertNotSame($uri, $clone);
        $this->assertEquals('tag', $clone->getFragment());
        $this->assertEquals('https://phower.com#tag', (string) $clone);

        $new = $uri->withFragment('other');
        $this->assertNotSame($uri, $new);
        $this->assertEquals('other', $new->getFragment());
        $this->assertEquals('https://phower.com#other', (string) $new);
    }

    public function testWithFragmentThrowsExceptionWhenFragmentIsNotAString()
    {
        $uri = new \Phower\Http\Uri('https://phower.com');
        $this->setExpectedException(\Phower\Http\Exception\InvalidArgumentException::class);
        $new = $uri->withFragment(80);
    }

    public function testFilterSchemeReturnsEmptyStringWhenSchemeIsEmpty()
    {
        $uri = new \Phower\Http\Uri('https://phower.com');
        $new = $uri->withScheme(' ');
        $this->assertEquals('', $new->getScheme());
    }

    public function testFilterSchemeThrowsExceptionWhenSchemeIsNotValid()
    {
        $uri = new \Phower\Http\Uri('https://phower.com');
        $this->setExpectedException(\Phower\Http\Exception\InvalidArgumentException::class);
        $new = $uri->withScheme('ftp');
    }

    public function testUrlEncodeCharEncodesAnyExtendedCharacter()
    {
        $uri = new \Phower\Http\Uri('https://phower.com/path with spaces');
        $this->assertEquals('/path%20with%20spaces', $uri->getPath());
    }

    public function testFilterQueryStripsOffQuestionMarkWhenItIsFirstCharacter()
    {
        $uri = new \Phower\Http\Uri('https://phower.com');
        $new = $uri->withQuery('?a=1');
        $this->assertEquals('a=1', $new->getQuery());
    }

    public function testFilterQueryAllowsQueryStringKeyWithoutValue()
    {
        $uri = new \Phower\Http\Uri('https://phower.com');
        $new = $uri->withQuery('?foo');
        $this->assertEquals('foo', $new->getQuery());
    }

    public function testFilterFragmentIgnoresInitialHash()
    {
        $uri = new \Phower\Http\Uri('https://phower.com');
        $new = $uri->withFragment('#tag');
        $this->assertEquals('tag', $new->getFragment());
    }

    public function testGetPortReturnsNullWhenItIsAStandardPort()
    {
        $uri = new \Phower\Http\Uri('https://phower.com:443');
        $this->assertNull($uri->getPort());
        $new = $uri->withScheme('');
        $this->assertEquals(443, $new->getPort());
    }
}
