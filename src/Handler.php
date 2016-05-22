<?php
/**
 * Effortless maintenance management (http://juliangut.com/janitor)
 *
 * @link https://github.com/juliangut/janitor for the canonical source repository
 *
 * @license https://github.com/juliangut/janitor/blob/master/LICENSE
 */

namespace Janitor;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Maintenance handler interface.
 */
interface Handler
{
    /**
     * Run handler.
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface      $response
     * @param Watcher                $watcher
     *
     * @return ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, Watcher $watcher);
}
