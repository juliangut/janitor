<?php

/*
 * janitor (http://juliangut.com/janitor).
 * Effortless maintenance management.
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/janitor
 * @author Julián Gutiérrez <juliangut@gmail.com>
 */

namespace Janitor\Excluder;

use Psr\Http\Message\ServerRequestInterface;

/**
 * HTTP request header value maintenance excluder.
 */
class Header implements ExcluderInterface
{
    /**
     * Request headers.
     *
     * @var array
     */
    protected $headers;

    /**
     * Header constructor.
     *
     * @param array|string $headers
     * @param string       $value
     */
    public function __construct($headers = null, $value = null)
    {
        if ($headers !== null && !is_array($headers)) {
            $headers = [$headers => $value];
        }

        if (is_array($headers)) {
            foreach ($headers as $headerName => $headerValue) {
                $this->addHeader($headerName, $headerValue);
            }
        }
    }

    /**
     * Add header.
     *
     * @param string $header
     * @param string $value
     *
     * @return $this
     */
    public function addHeader($header, $value = null)
    {
        if (trim($header) !== '') {
            $this->headers[trim($header)] = $value;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \RuntimeException
     */
    public function isExcluded(ServerRequestInterface $request)
    {
        if (!count($this->headers)) {
            throw new \RuntimeException('No headers defined in header excluder');
        }

        foreach ($this->headers as $header => $value) {
            if ($value === null && $request->hasHeader($header)) {
                return true;
            } elseif ($value !== null
                && (trim($value) === $request->getHeaderLine($header)
                    || @preg_match(trim($value), $request->getHeaderLine($header)))
            ) {
                return true;
            }
        }

        return false;
    }
}
