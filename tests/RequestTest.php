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
}
