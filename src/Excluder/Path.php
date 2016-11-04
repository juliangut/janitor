<?php

/*
 * janitor (http://juliangut.com/janitor).
 * Effortless maintenance management.
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/janitor
 * @author Julián Gutiérrez <juliangut@gmail.com>
 */

namespace Janitor\Excluder;

use Janitor\Excluder as ExcluderInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Maintenance excluder by path.
 */
class Path implements ExcluderInterface
{
    /**
     * List of paths to be excluded.
     *
     * @var array
     */
    protected $paths = [];

    /**
     * @param string|array|null $paths
     */
    public function __construct($paths = null)
    {
        if (!is_array($paths)) {
            $paths = [$paths];
        }

        foreach ($paths as $path) {
            $this->addPath($path);
        }
    }

    /**
     * Add path.
     *
     * @param string $path
     *
     * @return $this
     */
    public function addPath($path)
    {
        if (trim($path) !== '') {
            $this->paths[] = trim($path);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isExcluded(ServerRequestInterface $request)
    {
        $currentPath = $request->getUri()->getPath();

        foreach ($this->paths as $path) {
            if ($path === $currentPath || @preg_match($path, $currentPath) === 1) {
                return true;
            }
        }

        return false;
    }
}
