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

use Janitor\Watcher as WatcherInterface;

/**
 * File existance check for maintenance status watcher.
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
     * @param string|array|null $files
     */
    public function __construct($files = null)
    {
        if (!is_array($files)) {
            $files = [$files];
        }

        foreach ($files as $file) {
            $this->addFile($file);
        }
    }

    /**
     * Add file path.
     *
     * @param string $file
     *
     * @return $this
     */
    public function addFile($file)
    {
        if (trim($file) !== '') {
            $this->file = realpath(trim($file));
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isActive()
    {
        return file_exists($this->file) && is_file($this->file);
    }
}
