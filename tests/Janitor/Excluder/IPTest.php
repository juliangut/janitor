<?php
/**
 * Effortless maintenance management (http://juliangut.com/janitor)
 *
 * @link https://github.com/juliangut/janitor for the canonical source repository
 *
 * @license https://github.com/juliangut/janitor/blob/master/LICENSE
 */

namespace Janitor\Test\Excluder;

use Janitor\Excluder\IP;
use Zend\Diactoros\ServerRequestFactory;

/**
 * Class IPTest
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

    public function testCreation()
    {
        $excluder = new IP;
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
