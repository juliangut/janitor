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
     * Allowed proxies.
     *
     * @var array
     */
    protected $trustedProxies = [];

    /**
     * @param string|array|null $ips
     * @param string|array|null $trustedProxies
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($ips = null, $trustedProxies = null)
    {
        if (!is_array($ips)) {
            $ips = [$ips];
        }

        foreach ($ips as $ipAddress) {
            if (trim($ipAddress) !== '') {
                $this->addIP($ipAddress);
            }
        }

        if (!is_array($trustedProxies)) {
            $trustedProxies = [$trustedProxies];
        }

        foreach ($trustedProxies as $ipAddress) {
            if (trim($ipAddress) !== '') {
                $this->addTrustedProxy($ipAddress);
            }
        }
    }

    /**
     * Add IP.
     *
     * @param string $ipAddress
     *
     * @throws \InvalidArgumentException
     *
     * @return $this
     */
    public function addIP($ipAddress)
    {
        if (!$this->isValidIp($ipAddress)) {
            throw new \InvalidArgumentException(sprintf('"%s" is not a valid IP address', $ipAddress));
        }

        $this->ips[] = $ipAddress;

        return $this;
    }

    /**
     * Add Trusted proxy.
     *
     * @param string $ipAddress
     *
     * @throws \InvalidArgumentException
     *
     * @return $this
     */
    public function addTrustedProxy($ipAddress)
    {
        if (!$this->isValidIp($ipAddress)) {
            throw new \InvalidArgumentException(sprintf('"%s" is not a valid IP address', $ipAddress));
        }

        $this->trustedProxies[] = $ipAddress;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isExcluded(ServerRequestInterface $request)
    {
        $currentIP = $this->determineCurrentIp($request);

        foreach ($this->ips as $ipAddress) {
            if ($ipAddress === $currentIP) {
                return true;
            }
        }

        return false;
    }

    /**
     * Find client's IP.
     *
     * @param ServerRequestInterface $request
     *
     * @return string|null
     */
    protected function determineCurrentIp(ServerRequestInterface $request)
    {
        $inspectionHeaders = [
            'X-Forwarded-For',
            'X-Forwarded',
            'X-Cluster-Client-Ip',
            'Client-Ip',
        ];

        $currentIp = $this->getIpFromServerParams($request);

        if (!count($this->trustedProxies) || in_array($currentIp, $this->trustedProxies, true)) {
            $trustedIp = null;

            $headers = $inspectionHeaders;
            while ($trustedIp === null && $header = array_shift($headers)) {
                if ($request->hasHeader($header)) {
                    $ipAddress = trim(current(explode(',', $request->getHeaderLine($header))));

                    if ($this->isValidIp($ipAddress)) {
                        $trustedIp = $ipAddress;
                    }
                }
            }

            if ($trustedIp !== null) {
                $currentIp = $trustedIp;
            }
        }

        return $currentIp;
    }

    /**
     * Return current IP retrieved from server parameters.
     *
     * @param ServerRequestInterface $request
     *
     * @return string|null
     */
    private function getIpFromServerParams(ServerRequestInterface $request)
    {
        $currentIp = null;

        $serverParams = $request->getServerParams();
        if (isset($serverParams['REMOTE_ADDR']) && $this->isValidIp($serverParams['REMOTE_ADDR'])) {
            $currentIp = $serverParams['REMOTE_ADDR'];
        }

        return $currentIp;
    }

    /**
     * Check IP validity.
     *
     * @param string $ipAddress
     *
     * @return bool
     */
    private function isValidIp($ipAddress)
    {
        return filter_var($ipAddress, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6) !== false;
    }
}
