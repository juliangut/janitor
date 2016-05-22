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
