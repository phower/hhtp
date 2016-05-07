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
 * Message trait test case.
 *
 * @author Pedro Ferreira <pedro@phower.com>
 */
class MessageTraitTest extends \PHPUnit_Framework_TestCase
{

    public function testGetProtocolVersion()
    {
        $trait = $this->getMockForTrait(\Phower\Http\MessageTrait::class);
        $this->assertEquals('1.1', $trait->getProtocolVersion());
    }

    public function testWithProtocolVersion()
    {
        $trait = $this->getMockForTrait(\Phower\Http\MessageTrait::class);
        $clone = $trait->withProtocolVersion('1.0');
        $this->assertEquals('1.0', $clone->getProtocolVersion());
    }

    public function testGetHeaders()
    {
        $trait = $this->getMockForTrait(\Phower\Http\MessageTrait::class);
        $this->assertEquals([], $trait->getHeaders());
    }

    public function testWithHeader()
    {
        $trait = $this->getMockForTrait(\Phower\Http\MessageTrait::class);
        $trait = $trait->withHeader('Content-type', 'application/json');

        $expected = ['content-type' => 'Content-type'];
        $this->assertAttributeEquals($expected, 'headerNames', $trait);

        $expected = ['Content-type' => ['application/json']];
        $this->assertAttributeEquals($expected, 'headers', $trait);

        $this->setExpectedException(\Phower\Http\Exception\InvalidArgumentException::class);
        $trait->withHeader('Content-type', 123);
    }

    public function testWithAddedHeader()
    {
        $trait = $this->getMockForTrait(\Phower\Http\MessageTrait::class);
        $trait = $trait->withAddedHeader('Content-type', 'application/json');
        $trait = $trait->withAddedHeader('Content-type', 'application/hal+json');

        $expected = ['content-type' => 'Content-type'];
        $this->assertAttributeEquals($expected, 'headerNames', $trait);

        $expected = ['Content-type' => ['application/json', 'application/hal+json']];
        $this->assertAttributeEquals($expected, 'headers', $trait);

        $this->setExpectedException(\Phower\Http\Exception\InvalidArgumentException::class);
        $trait->withAddedHeader('Content-type', 123);
    }

    public function testWithoutHeader()
    {
        $trait = $this->getMockForTrait(\Phower\Http\MessageTrait::class);
        $trait = $trait->withHeader('Content-type', 'application/json');
        $trait = $trait->withHeader('Accept', 'application/hal+json');

        $expected = ['content-type' => 'Content-type', 'accept' => 'Accept'];
        $this->assertAttributeEquals($expected, 'headerNames', $trait);

        $expected = ['Content-type' => ['application/json'], 'Accept' => ['application/hal+json']];
        $this->assertAttributeEquals($expected, 'headers', $trait);

        $trait = $trait->withoutHeader('Accept');
        $expected = ['Content-type' => ['application/json']];
        $this->assertAttributeEquals($expected, 'headers', $trait);

        $clone = $trait->withoutHeader('Etag');
        $this->assertNotSame($trait, $clone);
        $this->assertEquals($trait, $clone);
    }

    public function testHasHeader()
    {
        $trait = $this->getMockForTrait(\Phower\Http\MessageTrait::class);
        $clone = $trait->withHeader('Content-type', 'application/json');

        $this->assertFalse($trait->hasHeader('Content-type'));
        $this->assertTrue($clone->hasHeader('Content-type'));
    }

    public function testGetHeader()
    {
        $trait = $this->getMockForTrait(\Phower\Http\MessageTrait::class);
        $clone = $trait->withHeader('Content-type', 'application/json');

        $expected = ['application/json'];
        $this->assertEquals($expected, $clone->getHeader('Content-type'));

        $this->assertEquals([], $clone->getHeader('Accept'));
    }

    public function testGetHeaderLine()
    {
        $trait = $this->getMockForTrait(\Phower\Http\MessageTrait::class);
        $clone = $trait->withHeader('Content-type', 'application/json');

        $expected = 'application/json';
        $this->assertEquals($expected, $clone->getHeaderLine('Content-type'));

        $this->assertNull($clone->getHeaderLine('Accept'));
    }

    public function testWithBody()
    {
        $trait = $this->getMockForTrait(\Phower\Http\MessageTrait::class);
        $body = $this->getMock(\Psr\Http\Message\StreamInterface::class);

        $clone = $trait->withBody($body);
        $this->assertSame($body, $clone->getBody());
    }
}
