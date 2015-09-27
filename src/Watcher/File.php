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
 * File existance check for maintenance status provider.
 */
class File implements WatcherInterface
{
    /**
     * File path.
     *
     * @var string
     */
    protected $file;

    /**
     * @param string $file
     */
    public function __construct($file)
    {
        $this->setFile($file);
    }

    /**
     * {@inheritDoc}
     */
    public function isActive()
    {
        return file_exists($this->file) && is_file($this->file);
    }

    /**
     * Set file path.
     *
     * @param string $file
     */
    public function setFile($file)
    {
        $this->file = realpath($file);

        return $this;
    }

    /**
     * Get file path.
     *
     * @return string
     */
    public function getFile()
    {
        return $this->file;
    }
}
