<?php

/*
 * janitor (http://juliangut.com/janitor).
 * Effortless maintenance management.
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/janitor
 * @author Julián Gutiérrez <juliangut@gmail.com>
 */

namespace Janitor\Watcher;

/**
 * Default maintenance watcher interface.
 */
interface WatcherInterface
{
    /**
     * Get maintenance mode active.
     *
     * @return bool
     */
    public function isActive();
}
