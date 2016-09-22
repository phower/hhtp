<?php

/**
 * Phower Http
 *
 * @version 1.0.0
 * @link https://github.com/phower/http Public Git repository
 * @copyright (c) 2015-2016, Pedro Ferreira <https://phower.com>
 * @license https://opensource.org/licenses/MIT MIT
 */

namespace Phower\Http;

use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

/**
 * Request trait
 *
 * Implements common methods defined by RequestInterface
 *
 * @author Pedro Ferreira <pedro@phower.com>
 */
trait RequestTrait
{

    /**
     * @var null|string
     */
    private $requestTarget;

    /**
     * @var string
     */
    private $method;

    /**
     * @var null|UriInterface
     */
    private $uri;

    /**
     * @var array
     */
    private $validMethods = [
        'CONNECT',
        'DELETE',
        'GET',
        'HEAD',
        'OPTIONS',
        'PATCH',
        'POST',
        'PUT',
        'TRACE',
    ];

    /**
     * Initialize a request
     *
     * @param \Psr\Http\Message\UriInterface|string $uri
     * @param string $method
     * @param \Psr\Http\Message\StreamInterface|string $body
     * @param array $headers
     */
    public function init($uri, $method, $body = 'php://memory', array $headers = [])
    {
        $this->uri = $this->validateUri($uri);
        $this->method = $this->validateMethod($method);
        $this->stream = $body instanceof StreamInterface ? $body : new Stream($body, 'r');

        list($this->headerNames, $filteredHeaders) = $this->filterHeaders($headers);
        $this->headers = $this->validateHeaders($filteredHeaders);
    }

    /**
     * Retrieves the message's request target.
     *
     * Retrieves the message's request-target either as it will appear (for
     * clients), as it appeared at request (for servers), or as it was
     * specified for the instance (see withRequestTarget()).
     *
     * In most cases, this will be the origin-form of the composed URI,
     * unless a value was provided to the concrete implementation (see
     * withRequestTarget() below).
     *
     * If no URI is available, and no request-target has been specifically
     * provided, this method MUST return the string "/".
     *
     * @return string
     */
    public function getRequestTarget()
    {
        if (null !== $this->requestTarget) {
            return $this->requestTarget;
        }

        $target = $this->uri->getPath();

        if ($this->uri->getQuery()) {
            $target .= '?' . $this->uri->getQuery();
        }

        return $target;
    }

    /**
     * Return an instance with the specific request-target.
     *
     * If the request needs a non-origin-form request-target — e.g., for
     * specifying an absolute-form, authority-form, or asterisk-form —
     * this method may be used to create an instance with the specified
     * request-target, verbatim.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * changed request target.
     *
     * @link http://tools.ietf.org/html/rfc7230#section-2.7 (for the various
     *     request-target forms allowed in request messages)
     * @param mixed $requestTarget
     * @return self
     */
    public function withRequestTarget($requestTarget)
    {
        if (preg_match('#\s#', $requestTarget)) {
            $message = 'Invalid request target provided; cannot contain whitespace';
            throw new Exception\InvalidArgumentException($message);
        }

        $clone = clone $this;
        $clone->requestTarget = $requestTarget;

        return $clone;
    }

    /**
     * Retrieves the HTTP method of the request.
     *
     * @return string Returns the request method.
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Return an instance with the provided HTTP method.
     *
     * While HTTP method names are typically all uppercase characters, HTTP
     * method names are case-sensitive and thus implementations SHOULD NOT
     * modify the given string.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * changed request method.
     *
     * @param string $method Case-sensitive method.
     * @return self
     * @throws \InvalidArgumentException for invalid HTTP methods.
     */
    public function withMethod($method)
    {
        $clone = clone $this;
        $clone->method = $this->validateMethod($method);

        return $clone;
    }

    /**
     * Retrieves the URI instance.
     *
     * This method MUST return a UriInterface instance.
     *
     * @link http://tools.ietf.org/html/rfc3986#section-4.3
     * @return UriInterface Returns a UriInterface instance
     *     representing the URI of the request.
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * Returns an instance with the provided URI.
     *
     * This method MUST update the Host header of the returned request by
     * default if the URI contains a host component. If the URI does not
     * contain a host component, any pre-existing Host header MUST be carried
     * over to the returned request.
     *
     * You can opt-in to preserving the original state of the Host header by
     * setting `$preserveHost` to `true`. When `$preserveHost` is set to
     * `true`, this method interacts with the Host header in the following ways:
     *
     * - If the the Host header is missing or empty, and the new URI contains
     *   a host component, this method MUST update the Host header in the returned
     *   request.
     * - If the Host header is missing or empty, and the new URI does not contain a
     *   host component, this method MUST NOT update the Host header in the returned
     *   request.
     * - If a Host header is present and non-empty, this method MUST NOT update
     *   the Host header in the returned request.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * new UriInterface instance.
     *
     * @link http://tools.ietf.org/html/rfc3986#section-4.3
     * @param UriInterface $uri New request URI to use.
     * @param bool $preserveHost Preserve the original state of the Host header.
     * @return self
     */
    public function withUri(UriInterface $uri, $preserveHost = false)
    {
        $clone = clone $this;
        $clone->uri = $uri;

        if ($preserveHost || !$uri->getHost()) {
            return $clone;
        }

        $host = $uri->getHost();

        if ($uri->getPort()) {
            $host .= ':' . $uri->getPort();
        }

        $clone->headerNames['host'] = 'Host';
        $clone->headers['Host'] = [$host];

        return $clone;
    }

    /**
     * Validate URI
     *
     * @param \Phower\Http\Uri|string $uri
     * @return \Phower\Http\Uri
     * @throws Exception\InvalidArgumentException
     */
    private function validateUri($uri)
    {
        if (!$uri instanceof UriInterface && !is_string($uri)) {
            $type = is_object($uri) ? get_class($uri) : gettype($uri);
            $message = sprintf('Argument "uri" must be an instance of "%s" or a string; "%s" was given.', UriInterface::class, $type);
            throw new Exception\InvalidArgumentException($message);
        }

        if (is_string($uri)) {
            $uri = new Uri($uri);
        }

        return $uri;
    }

    /**
     * Validate method
     *
     * @param string $method
     * @return string
     * @throws Exception\InvalidArgumentException
     */
    private function validateMethod($method)
    {
        if (!is_string($method)) {
            $type = is_object($method) ? get_class($method) : gettype($method);
            $message = sprintf('Argument "method" must be a string; "%s" was given.', $type);
            throw new Exception\InvalidArgumentException($message);
        }

        $method = strtoupper($method);

        if (!in_array($method, $this->validMethods, true)) {
            $methods = implode(', ', $this->validMethods);
            $message = sprintf('Invalid method "%s"; please use one of "%s".', $method, $methods);
            throw new Exception\InvalidArgumentException($message);
        }

        return $method;
    }

    /**
     * Validate headers
     *
     * @param array $headers
     * @return array
     */
    private function validateHeaders(array $headers)
    {
        foreach ($headers as $name => $headerValues) {
            HeaderSecurity::assertValidName($name);
            array_walk($headerValues, __NAMESPACE__ . '\HeaderSecurity::assertValid');
        }

        return $headers;
    }
}
