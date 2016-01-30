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
 * @covers \Janitor\Excluder\IP
 */
class IPTest extends \PHPUnit_Framework_TestCase
{
    protected $excludedIPs = [
        '98.139.183.24',
        '74.125.230.5',
        '204.79.197.200',
    ];

    /**
     * @covers \Janitor\Excluder\IP::__construct
     * @covers \Janitor\Excluder\IP::addIP
     * @covers \Janitor\Excluder\IP::isExcluded
     *
     * @expectedException \InvalidArgumentException
     */
    public function testCreation()
    {
        $excluder = new IP();
        $this->assertFalse($excluder->isExcluded(ServerRequestFactory::fromGlobals()));

        new IP(['invalidIP']);
    }

    /**
     * @covers \Janitor\Excluder\IP::__construct
     * @covers \Janitor\Excluder\IP::addIP
     * @covers \Janitor\Excluder\IP::isExcluded
     */
    public function testIsExcluded()
    {
        $request = ServerRequestFactory::fromGlobals();
        $request = $request->withHeader('Client-Ip', '74.125.230.5');
        $excluder = new IP($this->excludedIPs);

        $this->assertTrue($excluder->isExcluded($request));
    }

    /**
     * @covers \Janitor\Excluder\IP::isExcluded
     */
    public function testIsNotExcluded()
    {
        $request = ServerRequestFactory::fromGlobals(['REMOTE_ADDR' => '10.10.10.10']);
        $request = $request->withHeader('X-Forwarded', '80.80.80.80');
        $excluder = new IP($this->excludedIPs, ['10.10.10.10']);

        $this->assertFalse($excluder->isExcluded($request));
    }
}
