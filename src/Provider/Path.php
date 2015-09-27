<?php
/**
 * Effortless maintenance management (http://juliangut.com/janitor)
 *
 * @link https://github.com/juliangut/janitor for the canonical source repository
 * @license https://github.com/juliangut/janitor/blob/master/LICENSE
 */

namespace Janitor\Provider;

/**
 * Path provider.
 */
interface Path
{
    /**
     * Get path.
     *
     * @return string
     */
    public function getPath();
}
