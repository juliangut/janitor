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
 * IP based maintenance excluder.
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
     * IP constructor.
     *
     * @param string|array $ips
     * @param string|array $trustedProxies
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($ips = null, $trustedProxies = null)
    {
        if ($ips !== null && !is_array($ips)) {
            $ips = [$ips];
        }

        if (is_array($ips)) {
            foreach ($ips as $ipAddress) {
                $this->addIP($ipAddress);
            }
        }

        if ($trustedProxies !== null && !is_array($trustedProxies)) {
            $trustedProxies = [$trustedProxies];
        }

        if (is_array($trustedProxies)) {
            foreach ($trustedProxies as $ipAddress) {
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
        if (trim($ipAddress) !== '') {
            if (!$this->isValidIP($ipAddress)) {
                throw new \InvalidArgumentException(sprintf('"%s" is not a valid IP address', $ipAddress));
            }

            $this->ips[] = $ipAddress;
        }

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
        if (trim($ipAddress) !== '') {
            if (!$this->isValidIP($ipAddress)) {
                throw new \InvalidArgumentException(sprintf('"%s" is not a valid IP address', $ipAddress));
            }

            $this->trustedProxies[] = $ipAddress;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \RuntimeException
     */
    public function isExcluded(ServerRequestInterface $request)
    {
        if (!count($this->ips)) {
            throw new \RuntimeException('No IPs defined in IP excluder');
        }

        $currentIP = $this->determineCurrentIP($request);

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
     * @return string
     */
    protected function determineCurrentIP(ServerRequestInterface $request)
    {
        $inspectionHeaders = [
            'X-Forwarded-For',
            'X-Forwarded',
            'X-Cluster-Client-Ip',
            'Client-Ip',
        ];

        $currentIp = $this->getIPFromServerParams($request);

        if (!count($this->trustedProxies) || in_array($currentIp, $this->trustedProxies, true)) {
            $trustedIp = null;

            $headers = $inspectionHeaders;
            while ($trustedIp === null && $header = array_shift($headers)) {
                if ($request->hasHeader($header)) {
                    $ipAddress = trim(current(explode(',', $request->getHeaderLine($header))));

                    if ($this->isValidIP($ipAddress)) {
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
     * @return string
     */
    private function getIPFromServerParams(ServerRequestInterface $request)
    {
        $currentIp = null;

        $serverParams = $request->getServerParams();
        if (isset($serverParams['REMOTE_ADDR']) && $this->isValidIP($serverParams['REMOTE_ADDR'])) {
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
    private function isValidIP($ipAddress)
    {
        return filter_var($ipAddress, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6) !== false;
    }
}
