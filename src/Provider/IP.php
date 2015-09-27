<?php
/**
 * Effortless maintenance management (http://juliangut.com/janitor)
 *
 * @link https://github.com/juliangut/janitor for the canonical source repository
 * @license https://github.com/juliangut/janitor/blob/master/LICENSE
 */

namespace Janitor\Provider;

/**
 * IP provider.
 */
interface IP
{
    /**
     * Get IP address.
     *
     * @return string
     */
    public function getIPAddress();
}
