<?php

/*
 * janitor (http://juliangut.com/janitor).
 * Effortless maintenance management.
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/janitor
 * @author Julián Gutiérrez <juliangut@gmail.com>
 */

namespace Janitor\Test\Excluder;

use Janitor\Excluder\IP;
use Zend\Diactoros\ServerRequestFactory;

/**
 * IP based maintenance excluder tests.
 */
class IPTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    protected $excludedIPs = [
        '98.139.183.24',
        '74.125.230.5',
        '204.79.197.200',
    ];

    /**
     * @expectedException \RuntimeException
     */
    public function testNoIP()
    {
        $excluder = new IP();

        $excluder->isExcluded(ServerRequestFactory::fromGlobals());
    }

    public function testCreation()
    {
        $excluder = new IP('');
        $excluder->addIP('80.80.80.80');
        self::assertFalse($excluder->isExcluded(ServerRequestFactory::fromGlobals()));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testBadIp()
    {
        new IP(['invalidIP']);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testBadProxyIp()
    {
        new IP(null, 'badIp');
    }

    public function testIsExcluded()
    {
        $request = ServerRequestFactory::fromGlobals(['REMOTE_ADDR' => '74.125.230.5']);
        $request = $request->withHeader('Client-Ip', '74.125.230.5');
        $excluder = new IP($this->excludedIPs);

        self::assertTrue($excluder->isExcluded($request));
    }

    public function testIsNotExcluded()
    {
        $request = ServerRequestFactory::fromGlobals();
        $request = $request->withHeader('X-Forwarded', '80.80.80.80');
        $excluder = new IP($this->excludedIPs, ['10.10.10.10']);

        self::assertFalse($excluder->isExcluded($request));
    }
}
