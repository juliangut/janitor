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
use Janitor\Provider\Path as PathProvider;
use Janitor\Provider\Path\Basic as BasicPathProvider;

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
     * Path provider.
     *
     * @var \Janitor\Provider\Path
     */
    protected $provider;

    /**
     * @param array                       $paths
     * @param \Janitor\Provider\Path|null $provider
     */
    public function __construct(array $paths = [], PathProvider $provider = null)
    {
        foreach ($paths as $path) {
            $this->addPath($path);
        }

        $this->provider = $provider;
    }

    /**
     * Add path.
     *
     * @param string $path
     */
    public function addPath($path)
    {
        $this->paths[] = '/' . trim($path, '/');

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isExcluded()
    {
        if (!$this->provider instanceof PathProvider) {
            $this->provider = new BasicPathProvider();
        }

        $currentPath = '/' . trim($this->provider->getPath(), '/');

        foreach ($this->paths as $path) {
            if ($path === $currentPath) {
                return true;
            }
        }

        return false;
    }
}
