<?php
/**
 * Effortless maintenance management (http://juliangut.com/janitor)
 *
 * @link https://github.com/juliangut/janitor for the canonical source repository
 *
 * @license https://github.com/juliangut/janitor/blob/master/LICENSE
 */

namespace Janitor\Excluder;

use Janitor\Excluder as ExcluderInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Maintenance excluder by request header value
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
     * @param array|string|null $headers
     * @param string|null       $value
     */
    public function __construct($headers = null, $value = null)
    {
        if (!is_array($headers)) {
            $headers = [$headers => $value];
        }

        foreach ($headers as $headerName => $headerValue) {
            $this->addHeader($headerName, $headerValue);
        }
    }

    /**
     * Add header.
     *
     * @param string      $header
     * @param string|null $value
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
     */
    public function isExcluded(ServerRequestInterface $request)
    {
        foreach ($this->headers as $header => $value) {
            if ($value === null && $request->hasHeader($header)) {
                return true;
            } elseif ($value !== null && (trim($value) === $request->getHeaderLine($header)
                || @preg_match(trim($value), $request->getHeaderLine($header)))
            ) {
                return true;
            }
        }

        return false;
    }
}
