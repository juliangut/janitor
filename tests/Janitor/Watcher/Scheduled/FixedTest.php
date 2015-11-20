<?php
/**
 * Effortless maintenance management (http://juliangut.com/janitor)
 *
 * @link https://github.com/juliangut/janitor for the canonical source repository
 * @license https://github.com/juliangut/janitor/blob/master/LICENSE
 */

namespace Janitor\Test\Watcher\Scheduled;

use Janitor\Watcher\Scheduled\Fixed;

/**
 * @covers \Janitor\Watcher\Scheduled\Fixed
 */
class FixedTest extends \PHPUnit_Framework_TestCase
{
    protected $watcher;

    public function setUp()
    {
        $this->watcher = new Fixed('yesterday', 'tomorrow');
    }

    /**
     * @covers \Janitor\Watcher\Scheduled\Fixed::isActive
     * @covers \Janitor\Watcher\Scheduled\Fixed::isActive
     * @covers \Janitor\Watcher\Scheduled\Fixed::isScheduled
     * @covers \Janitor\Watcher\Scheduled\Fixed::getScheduledTimes
     */
    public function testDefaults()
    {
        $this->assertTrue($this->watcher->isActive());
        $this->assertFalse($this->watcher->isScheduled());
        $this->assertEquals([], $this->watcher->getScheduledTimes());
    }

    /**
     * @covers \Janitor\Watcher\Scheduled\Fixed::setStart
     * @covers \Janitor\Watcher\Scheduled\Fixed::getStart
     * @covers \Janitor\Watcher\Scheduled\Fixed::setEnd
     * @covers \Janitor\Watcher\Scheduled\Fixed::getEnd
     */
    public function testMutatorsAccessors()
    {
        $start = new \DateTime();
        $end = clone $start;
        $end->add(new \DateInterval('PT1H'));

        $this->watcher->setStart($start);
        $this->assertEquals($start, $this->watcher->getStart());
        $this->watcher->setEnd($end);
        $this->assertEquals($end, $this->watcher->getEnd());
    }

    /**
     * @covers \Janitor\Watcher\Scheduled\Fixed::setStart
     * @covers \Janitor\Watcher\Scheduled\Fixed::setEnd
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidStartDate()
    {
        $this->watcher->setEnd('now');

        $start = new \DateTime();
        $start->add(new \DateInterval('P10D'));
        $this->watcher->setStart($start);
    }

    /**
     * @covers \Janitor\Watcher\Scheduled\Fixed::setStart
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidStartString()
    {
        $this->watcher->setStart('wow');
    }

    /**
     * @covers \Janitor\Watcher\Scheduled\Fixed::setStart
     * @covers \Janitor\Watcher\Scheduled\Fixed::setEnd
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidEndDate()
    {
        $this->watcher->setStart('now');

        $end = new \DateTime();
        $end->sub(new \DateInterval('P10D'));
        $this->watcher->setEnd($end);
    }

    /**
     * @covers \Janitor\Watcher\Scheduled\Fixed::setEnd
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidEndString()
    {
        $this->watcher->setEnd('wow');
    }

    /**
     * @covers \Janitor\Watcher\Scheduled\Fixed::isActive
     * @covers \Janitor\Watcher\Scheduled\Fixed::isScheduled
     * @covers \Janitor\Watcher\Scheduled\Fixed::getScheduledTimes
     */
    public function testScheduledTime()
    {
        $this->assertEquals([], $this->watcher->getScheduledTimes());

        $start = new \DateTime();
        $start->add(new \DateInterval('P1D'));
        $end = clone $start;
        $this->watcher->setEnd($end);
        $this->watcher->setStart($start);
        $this->assertEquals([['start' => $start, 'end' => $end]], $this->watcher->getScheduledTimes());

        $start = new \DateTime();
        $start->sub(new \DateInterval('P1D'));
        $end = clone $start;
        $this->watcher->setStart($start);
        $this->watcher->setEnd($end);
        $this->assertEquals([], $this->watcher->getScheduledTimes());
    }

    /**
     * @covers \Janitor\Watcher\Scheduled\Fixed::setStart
     * @covers \Janitor\Watcher\Scheduled\Fixed::setEnd
     * @covers \Janitor\Watcher\Scheduled\Fixed::isActive
     * @covers \Janitor\Watcher\Scheduled\Fixed::isScheduled
     */
    public function testBeforeTime()
    {
        $start = new \DateTime();
        $start->add(new \DateInterval('P1D'));
        $end = clone $start;
        $watcher = new Fixed($start, $end);

        $this->assertFalse($watcher->isActive());
        $this->assertTrue($watcher->isScheduled());
    }

    /**
     * @covers \Janitor\Watcher\Scheduled\Fixed::setStart
     * @covers \Janitor\Watcher\Scheduled\Fixed::setEnd
     * @covers \Janitor\Watcher\Scheduled\Fixed::isActive
     * @covers \Janitor\Watcher\Scheduled\Fixed::isScheduled
     */
    public function testAfterTime()
    {
        $start = new \DateTime();
        $start->sub(new \DateInterval('P1D'));
        $end = clone $start;
        $watcher = new Fixed($start, $end);

        $this->assertFalse($watcher->isActive());
        $this->assertFalse($watcher->isScheduled());

    }
}
