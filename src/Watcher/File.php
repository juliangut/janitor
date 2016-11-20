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
 * File existence maintenance status watcher.
 */
class File implements WatcherInterface
{
    /**
     * Files.
     *
     * @var array
     */
    protected $files;

    /**
     * File constructor.
     *
     * @param string|array $files
     */
    public function __construct($files = null)
    {
        if ($files !== null && !is_array($files)) {
            $files = [$files];
        }

        if (is_array($files)) {
            foreach ($files as $file) {
                $this->addFile($file);
            }
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
            $this->files[] = trim($file);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \RuntimeException
     */
    public function isActive()
    {
        if (!count($this->files)) {
            throw new \RuntimeException('At least one file path must be defined');
        }

        foreach ($this->files as $file) {
            $file = realpath($file);

            if (file_exists($file) && is_file($file)) {
                return true;
            }
        }

        return false;
    }
}
