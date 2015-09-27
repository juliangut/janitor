<?php
/**
 * Effortless maintenance management (http://juliangut.com/janitor)
 *
 * @link https://github.com/juliangut/janitor for the canonical source repository
 * @license https://github.com/juliangut/janitor/blob/master/LICENSE
 */

namespace Janitor\Provider\IP;

use Janitor\Provider\IP as IPInterface;

/**
 * Basic IP provider.
 */
class Basic implements IPInterface
{
    /**
     * {inheritDoc}
     */
    public function getIPAddress()
    {
        if (isset($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        }

        $ipAddress = $this->getIpFromProxy();
        if ($ipAddress) {
            return $ipAddress;
        }

        if (isset($_SERVER['REMOTE_ADDR'])) {
            return $_SERVER['REMOTE_ADDR'];
        }

        return '';
    }

    /**
     * Get IP address by traversing forwarded proxies
     *
     * @return string|false
     */
    protected function getIpFromProxy()
    {
        $proxyHeader = 'HTTP_X_FORWARDED_FOR';

        if (!isset($_SERVER[$proxyHeader]) || empty($_SERVER[$proxyHeader])) {
            return false;
        }

        $ips = explode(',', $_SERVER[$proxyHeader]);
        $ips = array_map('trim', $ips);

        return array_pop($ips);
    }
}
