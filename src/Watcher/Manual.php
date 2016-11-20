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
 * Manual maintenance status watcher.
 */
class Manual implements WatcherInterface
{
    /**
     * Maintenance mode active.
     *
     * @var bool
     */
    protected $active = false;

    /**
     * Manual constructor.
     *
     * @param bool $active
     */
    public function __construct($active = false)
    {
        $this->setActive($active);
    }

    /**
     * Set maintenance mode active.
     *
     * @param bool $active
     *
     * @return $this
     */
    public function setActive($active = true)
    {
        $this->active = (bool) $active;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isActive()
    {
        return $this->active;
    }
}
