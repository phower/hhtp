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
 * Response class test case.
 *
 * @author Pedro Ferreira <pedro@phower.com>
 */
class ResponseTest extends \PHPUnit_Framework_TestCase
{

    public function testClassImplementsPsr7ResponseInterface()
    {
        $response = $this->createMock(\Phower\Http\Response::class);
        $this->assertInstanceOf(\Psr\Http\Message\ResponseInterface::class, $response);
    }

    /**
     * @dataProvider constructProvider
     */
    public function testConstructCanInstantiateObject($body = 'php://memory', $status = 200, array $headers = [])
    {
        $response = new \Phower\Http\Response($body, $status, $headers);

        $this->assertAttributeInstanceOf(\Psr\Http\Message\StreamInterface::class, 'stream', $response);
        $this->assertAttributeEquals($status, 'statusCode', $response);
        $this->assertAttributeEquals($status, 'statusCode', $response);
        $this->assertAttributeInternalType('array', 'headers', $response);
        $this->assertAttributeCount(count($headers), 'headers', $response);
    }

    public function constructProvider()
    {
        $body = $this->createMock(\Psr\Http\Message\StreamInterface::class);

        return [
            [],
            ['php://memory'],
            [$body],
            [$body, 304],
            [$body, 200, []],
            [$body, 200, ['content-type' => 'text/html']],
        ];
    }

    public function testConstructRaisesExceptionWhenBodyIsNotValid()
    {
        $this->setExpectedException(\Phower\Http\Exception\InvalidArgumentException::class);
        $response = new \Phower\Http\Response([]);
    }

    public function testValidateStatusRaisesExceptionWhenStatusCodeIsNotValid()
    {
        $this->setExpectedException(\Phower\Http\Exception\InvalidArgumentException::class);
        $response = new \Phower\Http\Response('php://memory', 'abc');
    }

    public function testWithStatusReturnsClonedResponseWithGivenStatusCode()
    {
        $response = new \Phower\Http\Response();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('OK', $response->getReasonPhrase());

        $code = 333;
        $reasonPhrase = 'Some phrase';

        $clone = $response->withStatus($code, $reasonPhrase);
        $this->assertNotSame($clone, $response);
        $this->assertAttributeEquals($code, 'statusCode', $clone);
        $this->assertAttributeEquals($reasonPhrase, 'reasonPhrase', $clone);
    }
}
