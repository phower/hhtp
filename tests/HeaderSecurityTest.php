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
 * Header security class test case.
 *
 * @author Pedro Ferreira <pedro@phower.com>
 */
class HeaderSecurityTest extends \PHPUnit_Framework_TestCase
{

    public function testFilter()
    {
        $this->assertEquals('application/json', \Phower\Http\HeaderSecurity::filter('application/json'));
        $this->assertEquals(sprintf('some%s%s%smore', chr(13), chr(10), chr(9)), \Phower\Http\HeaderSecurity::filter(sprintf('some%s%s%smore', chr(13), chr(10), chr(9))));
        $this->assertEquals('somemore', \Phower\Http\HeaderSecurity::filter(sprintf('some%smore', chr(11))));
    }

    public function testIsValid()
    {
        $this->assertTrue(\Phower\Http\HeaderSecurity::isValid('application/json'));
        $this->assertFalse(\Phower\Http\HeaderSecurity::isValid("some\nvalue"));
        $this->assertFalse(\Phower\Http\HeaderSecurity::isValid("some\rvalue"));
        $this->assertFalse(\Phower\Http\HeaderSecurity::isValid("some\r\nvalue"));
        $this->assertTrue(\Phower\Http\HeaderSecurity::isValid("some\r\n value"));
        $this->assertFalse(\Phower\Http\HeaderSecurity::isValid(chr(127)));
        $this->assertFalse(\Phower\Http\HeaderSecurity::isValid(chr(255)));
    }

    public function testAssertValid()
    {
        $this->setExpectedException(\Phower\Http\Exception\InvalidArgumentException::class);
        \Phower\Http\HeaderSecurity::assertValid("some\r\nvalue");
    }

    public function testAssertValidName()
    {
        $this->setExpectedException(\Phower\Http\Exception\InvalidArgumentException::class);
        \Phower\Http\HeaderSecurity::assertValidName("some\r\nvalue");
    }
}
