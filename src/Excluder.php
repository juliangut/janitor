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

interface Excluder
{
    /**
     * Determines if is excluded from maintenance mode.
     *
     * @param |Psr\Http\Message\ServerRequestInterface $request
     *
     * @return bool
     */
    public function isExcluded(ServerRequestInterface $request);
}
