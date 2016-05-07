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
 * Response class test case.
 *
 * @author Pedro Ferreira <pedro@phower.com>
 */
class ResponseTest extends \PHPUnit_Framework_TestCase
{

    public function testClassImplementsPsr7ResponseInterface()
    {
        $message = $this->getMockBuilder(\Phower\Http\Response::class)
                ->disableOriginalConstructor()
                ->getMock();

        $this->assertInstanceOf(\Psr\Http\Message\ResponseInterface::class, $message);
    }
}
