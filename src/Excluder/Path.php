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

use Psr\Http\Message\ServerRequestInterface;

/**
 * Path based maintenance excluder.
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
     * Path constructor.
     *
     * @param string|array $paths
     */
    public function __construct($paths = null)
    {
        if ($paths !== null && !is_array($paths)) {
            $paths = [$paths];
        }

        if (is_array($paths)) {
            foreach ($paths as $path) {
                $this->addPath($path);
            }
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
        if (!count($this->paths)) {
            throw new \RuntimeException('No paths defined in path excluder');
        }

        $currentPath = $request->getUri()->getPath();

        foreach ($this->paths as $path) {
            if ($path === $currentPath || @preg_match($path, $currentPath) === 1) {
                return true;
            }
        }

        return false;
    }
}
