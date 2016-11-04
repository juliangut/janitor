<?php

/*
 * janitor (http://juliangut.com/janitor).
 * Effortless maintenance management.
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/janitor
 * @author Julián Gutiérrez <juliangut@gmail.com>
 */

namespace Janitor;

use Psr\Http\Message\ServerRequestInterface;

/**
 * Maintenance excluder interface.
 */
interface Excluder
{
    /**
     * Determines if is excluded from maintenance mode.
     *
     * @param ServerRequestInterface $request
     *
     * @return bool
     */
    public function isExcluded(ServerRequestInterface $request);
}
