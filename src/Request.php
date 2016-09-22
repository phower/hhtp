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

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

/**
 * Representation of an outgoing, client-side request.
 *
 * Per the HTTP specification, this interface includes properties for
 * each of the following:
 *
 * - Protocol version
 * - HTTP method
 * - URI
 * - Headers
 * - Message body
 *
 * During construction, implementations MUST attempt to set the Host header from
 * a provided URI if no Host header is provided.
 *
 * Requests are considered immutable; all methods that might change state MUST
 * be implemented such that they retain the internal state of the current
 * message and return an instance that contains the changed state.
 *
 * @author Pedro Ferreira <pedro@phower.com>
 */
class Request implements RequestInterface
{

    use MessageTrait,
        RequestTrait;

    /**
     * Create a new request
     *
     * @param \Psr\Http\Message\UriInterface|string $uri
     * @param string $method
     * @param \Psr\Http\Message\StreamInterface|string $body
     * @param array $headers
     */
    public function __construct($uri, $method, $body = 'php://memory', array $headers = [])
    {
        $this->init($uri, $method, $body, $headers);
    }
}
