<?php
/**
 * Effortless maintenance management (http://juliangut.com/janitor)
 *
 * @link https://github.com/juliangut/janitor for the canonical source repository
 * @license https://github.com/juliangut/janitor/blob/master/LICENSE
 */

namespace Janitor\Watcher;

use Janitor\Watcher as WatcherInterface;

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
     */
    public function setActive($active = true)
    {
        $this->active = boolval($active);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function isActive()
    {
        return $this->active;
    }
}
