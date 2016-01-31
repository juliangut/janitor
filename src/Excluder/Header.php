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
     * Request header.
     *
     * @var string
     */
    protected $header;

    /**
     * Request header value.
     *
     * @var string
     */
    protected $value;

    /**
     * @param string      $header
     * @param string|null $value
     */
    public function __construct($header, $value = null)
    {
        $this->header = $header;
        $this->value = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function isExcluded(ServerRequestInterface $request)
    {
        return $this->value === null
            ? $request->hasHeader($this->header)
            : $this->value === $request->getHeaderLine($this->header)
                || @preg_match($this->value, $request->getHeaderLine($this->header)) === 1;
    }
}
