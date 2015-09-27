<?php
/**
 * Effortless maintenance management (http://juliangut.com/janitor)
 *
 * @link https://github.com/juliangut/janitor for the canonical source repository
 * @license https://github.com/juliangut/janitor/blob/master/LICENSE
 */

namespace Janitor\Excluder;

use Janitor\Excluder as ExcluderInterface;
use Janitor\Provider\IP as IPProvider;
use Janitor\Provider\IP\Basic as BasicIPProvider;

/**
 * Maintenance excluder by route
 */
class IP implements ExcluderInterface
{
    /**
     * List of IPs to be excluded.
     *
     * @var array
     */
    protected $ips = [];

    /**
     * IP provider.
     *
     * @var \Janitor\Provider\IP
     */
    protected $provider;

    /**
     * @param array $ips
     * @param \Janitor\Provider\IP $provider
     */
    public function __construct(array $ips = [], IPProvider $provider = null)
    {
        foreach ($ips as $ipAddress) {
            $this->addIP($ipAddress);
        }

        if (!$provider instanceof IPProvider) {
            $provider = new BasicIPProvider();
        }

        $this->provider = $provider;
    }

    /**
     * {@inheritDoc}
     */
    public function isExcluded()
    {
        $currentIP = $this->provider->getIPAddress();

        foreach ($this->ips as $ip) {
            if ($ip === $currentIP) {
                return true;
            }
        }

        return false;
    }

    /**
     * Add IP.
     *
     * @param string $ipAddress
     * @throws \InvalidArgumentException
     */
    public function addIP($ipAddress)
    {
        if (!filter_var($ipAddress, FILTER_VALIDATE_IP)) {
            throw new \InvalidArgumentException(sprintf('"%s" is not a valid IP address', $ipAddress));
        }

        $this->ips[] = $ipAddress;

        return $this;
    }
}
