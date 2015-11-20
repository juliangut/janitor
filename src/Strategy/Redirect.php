<?php
/**
 * Effortless maintenance management (http://juliangut.com/janitor)
 *
 * @link https://github.com/juliangut/janitor for the canonical source repository
 * @license https://github.com/juliangut/janitor/blob/master/LICENSE
 */

namespace Janitor\Strategy;

use Janitor\Strategy as StrategyInterface;
use Janitor\Watcher;

/**
 * Redirect maintenance strategy.
 */
class Redirect implements StrategyInterface
{
    private $location;

    /**
     * @param string $location
     */
    public function __construct($location)
    {
        $this->location = (string) $location;
    }

    /**
     * {@inheritDoc}
     */
    public function handle(Watcher $watcher)
    {
        if (!headers_sent()) {
            header('Location: ' . $this->location);
        }
    }
}
