<?php
/**
 * Effortless maintenance management (http://juliangut.com/janitor)
 *
 * @link https://github.com/juliangut/janitor for the canonical source repository
 *
 * @license https://github.com/juliangut/janitor/blob/master/LICENSE
 */

namespace Janitor\Excluder;

use Janitor\Excluder as ExcluderInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Maintenance excluder by path
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
     * @param array $paths
     */
    public function __construct(array $paths = [])
    {
        foreach ($paths as $path) {
            $this->addPath($path);
        }
    }

    /**
     * Add path.
     *
     * @param string $path
     */
    public function addPath($path)
    {
        $this->paths[] = trim($path, '/');

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isExcluded(ServerRequestInterface $request)
    {
        $currentPath = trim($request->getUri()->getPath(), '/');

        foreach ($this->paths as $path) {
            if ($path === $currentPath) {
                return true;
            }
        }

        return false;
    }
}
