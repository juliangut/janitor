<?php
/**
 * Effortless maintenance management (http://juliangut.com/janitor)
 *
 * @link https://github.com/juliangut/janitor for the canonical source repository
 * @license https://github.com/juliangut/janitor/blob/master/LICENSE
 */

namespace Janitor\Test\Excluder;

use Janitor\Excluder\IP;

/**
 * @covers Janitor\Excluder\IP
 */
class IPTest extends \PHPUnit_Framework_TestCase
{
    protected $excludedIPs = array(
        '98.139.183.24',
        '74.125.230.5',
        '204.79.197.200',
    );

    /**
     * @covers Janitor\Excluder\IP::__construct
     * @covers Janitor\Excluder\IP::addIP
     * @expectedException InvalidArgumentException
     */
    public function testCreationInvalidIP()
    {
        new IP();
        new IP(['invalidIP']);
    }

    /**
     * @covers Janitor\Excluder\IP::__construct
     * @covers Janitor\Excluder\IP::addIP
     * @covers Janitor\Excluder\IP::isExcluded
     */
    public function testIsExcluded()
    {
        $ipProvider = $this->getMock('Janitor\\Provider\\IP\\Basic');
        $ipProvider->expects($this->once())->method('getIpAddress')->will($this->returnValue('98.139.183.24'));

        $exclusion = new IP($this->excludedIPs, $ipProvider);

        $this->assertTrue($exclusion->isExcluded());
    }

    /**
     * @covers Janitor\Excluder\IP::isExcluded
     */
    public function testIsNotExcluded()
    {
        $ipProvider = $this->getMock('Janitor\\Provider\\IP\\Basic');
        $ipProvider->expects($this->once())->method('getIpAddress')->will($this->returnValue('127.0.0.1'));

        $exclusion = new IP($this->excludedIPs, $ipProvider);

        $this->assertFalse($exclusion->isExcluded());
    }
}
