<?php
/**
 * Effortless maintenance management (http://juliangut.com/janitor)
 *
 * @link https://github.com/juliangut/janitor for the canonical source repository
 * @license https://github.com/juliangut/janitor/blob/master/LICENSE
 */

namespace Janitor\Test;

use Janitor\Janitor;

/**
 * @covers Janitor\Janitor
 */
class JanitorTest extends \PHPUnit_Framework_TestCase
{
    protected $janitor;

    public function setUp()
    {
        $watcher = $this->getMock('Janitor\\Watcher');
        $watcher->expects($this->any())->method('isActive')->will($this->returnValue(false));
        $watcher->expects($this->any())->method('isBlocker')->will($this->returnValue(true));

        $excluder = $this->getMock('Janitor\\Excluder');
        $excluder->expects($this->any())->method('isExcluded')->will($this->returnValue(false));

        $this->janitor = new Janitor([$watcher], [$excluder]);
    }

    /**
     * @covers Janitor\Janitor::addWatcher
     * @covers Janitor\Janitor::addExcluder
     * @covers Janitor\Janitor::inMaintenance
     * @covers Janitor\Janitor::getActiveWatcher
     * @covers Janitor\Janitor::isExcluded
     */
    public function testMaintenanceStatus()
    {
        $this->assertFalse($this->janitor->inMaintenance());

        $this->assertNull($this->janitor->getActiveWatcher());

        $this->assertFalse($this->janitor->isExcluded());

        $watcher = $this->getMock('Janitor\\Watcher');
        $watcher->expects($this->any())->method('isActive')->will($this->returnValue(true));
        $this->janitor->addWatcher($watcher);

        $this->assertTrue($this->janitor->inMaintenance());
        $this->assertEquals($watcher, $this->janitor->getActiveWatcher());

        $excluder = $this->getMock('Janitor\\Excluder');
        $excluder->expects($this->once())->method('isExcluded')->will($this->returnValue(true));
        $this->janitor->addExcluder($excluder);

        $this->assertTrue($this->janitor->isExcluded());
    }

    /**
     * @covers Janitor\Janitor::getScheduledTimes
     */
    public function testScheduled()
    {
        $start = new \DateTime();
        $start->add(new \DateInterval('P1D'));
        $end = clone $start;
        $end->add(new \DateInterval('PT2H'));
        $scheduledTimes = [['start' => $start, 'end' => $end]];

        $start = new \DateTime();
        $start->add(new \DateInterval('PT2H'));
        $end = clone $start;
        $end->add(new \DateInterval('PT1H'));
        array_unshift($scheduledTimes, ['start' => $start, 'end' => $end]);

        $start = new \DateTime();
        $start->add(new \DateInterval('P1D'));
        $start->add(new \DateInterval('PT1H'));
        $end = clone $start;
        $end->add(new \DateInterval('PT3H'));
        $scheduledTimes[] = ['start' => $start, 'end' => $end];
        $scheduledTimes[] = ['start' => $start, 'end' => $end];

        foreach ($scheduledTimes as $time) {
            $watcher = $this->getMock('Janitor\\ScheduledWatcher');
            $watcher->expects($this->any())->method('isActive')->will($this->returnValue(true));
            $watcher->expects($this->any())->method('isScheduled')->will($this->returnValue(true));
            $watcher->expects($this->any())->method('getScheduledTimes')->will($this->returnValue([$time]));

            $this->janitor->addWatcher($watcher);
        }

        $this->assertEquals($scheduledTimes, $this->janitor->getScheduledTimes());
    }

    /**
     * @covers Janitor\Janitor::handle
     */
    public function testHandle()
    {
        $this->assertFalse($this->janitor->handle());

        $watcher = $this->getMock('Janitor\\Watcher');
        $watcher->expects($this->any())->method('isActive')->will($this->returnValue(true));
        $watcher->expects($this->any())->method('isBlocker')->will($this->returnValue(true));

        $this->janitor->addWatcher($watcher);

        $strategy = $this->getMock('Janitor\\Strategy');
        $strategy->expects($this->any())->method('handle');

        $this->janitor->setStrategy($strategy);

        $this->assertTrue($this->janitor->handle());
    }
}
