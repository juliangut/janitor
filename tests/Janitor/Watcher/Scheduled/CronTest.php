<?php
/**
 * Effortless maintenance management (http://juliangut.com/janitor)
 *
 * @link https://github.com/juliangut/janitor for the canonical source repository
 *
 * @license https://github.com/juliangut/janitor/blob/master/LICENSE
 */

namespace Janitor\Test\Watcher\Scheduled;

use Janitor\Watcher\Scheduled\Cron;

/**
 * @covers Janitor\Watcher\Scheduled\Cron
 */
class CronTest extends \PHPUnit_Framework_TestCase
{
    protected $watcher;

    public function setUp()
    {
        $this->watcher = new Cron('0 0 1 1 *', 'PT1M');
    }

    /**
     * @covers \Janitor\Watcher\Scheduled\Cron::setExpression
     * @covers \Janitor\Watcher\Scheduled\Cron::getExpression
     * @expectedException \InvalidArgumentException
     */
    public function testExpressionMutatorsAccessors()
    {
        $this->watcher->setExpression(Cron::ANNUALLY);
        $this->assertEquals('0 0 1 1 *', $this->watcher->getExpression());

        $this->watcher->setExpression('invalidExpression');
    }

    /**
     * @covers \Janitor\Watcher\Scheduled\Cron::getStart
     * @covers \Janitor\Watcher\Scheduled\Cron::getEnd
     * @covers \Janitor\Watcher\Scheduled\Cron::setInterval
     * @covers \Janitor\Watcher\Scheduled\Cron::getInterval
     * @expectedException \InvalidArgumentException
     */
    public function testIntervalMutatorsAccessors()
    {
        $this->watcher->setExpression(Cron::YEARLY);

        $this->assertNull($this->watcher->getStart());
        $this->assertNull($this->watcher->getEnd());

        $this->watcher->setInterval('P1Y');

        $start = new \DateTime();
        $start->setDate($start->format('Y'), 1, 1);
        $end = clone $start;
        $end->add(new \DateInterval('P1Y'));

        $this->assertEquals($start->format('Y m d'), $this->watcher->getStart()->format('Y m d'));
        $this->assertEquals($end->format('Y m d'), $this->watcher->getEnd()->format('Y m d'));

        $this->assertEquals(new \DateInterval('P1Y'), $this->watcher->getInterval());
        $this->watcher->setInterval('invalidDateInterval');
    }

    /**
     * @covers \Janitor\Watcher\Scheduled\Cron::isActive
     */
    public function testIsNotActive()
    {
        $time   = new \DateTime();
        $minute = $time->format('i');
        $hour   = $time->format('H');
        $day    = $time->format('d');
        $month  = $time->format('m');

        $this->watcher->setInterval('PT10S');

        $this->watcher->setExpression(sprintf('%s * * * *', $minute - 10 > -1 ? $minute - 10 : 50));
        $this->assertFalse($this->watcher->isActive());

        $this->watcher->setExpression(sprintf('* %s * * *', $hour - 1 > -1 ? $hour - 1 : 23));
        $this->assertFalse($this->watcher->isActive());

        $this->watcher->setExpression(sprintf('* * %s * *', $day - 1 > -1 ? $day - 1 : 28));
        $this->assertFalse($this->watcher->isActive());

        $this->watcher->setExpression(sprintf('* * * %s *', $month - 1 > -1 ? $month - 1 : 12));
        $this->assertFalse($this->watcher->isActive());
    }

    /**
     * @covers \Janitor\Watcher\Scheduled\Cron::isActive
     */
    public function testIsActive()
    {
        $time = new \DateTime();
        $minute = $time->format('i');
        $hour   = $time->format('H');
        $day    = $time->format('d');
        $month  = $time->format('m');

        $this->watcher->setInterval('PT1M');
        $this->watcher->setExpression(sprintf('%s * * * *', $minute));
        $this->assertTrue($this->watcher->isActive());

        $this->watcher->setInterval('PT1H');
        $this->watcher->setExpression(sprintf('* %s * * *', $hour));
        $this->assertTrue($this->watcher->isActive());

        $this->watcher->setInterval('P1D');
        $this->watcher->setExpression(sprintf('* * %s * *', $day));
        $this->assertTrue($this->watcher->isActive());

        $this->watcher->setInterval('P1M');
        $this->watcher->setExpression(sprintf('* * * %s *', $month));
        $this->assertTrue($this->watcher->isActive());
    }

    /**
     * @covers \Janitor\Watcher\Scheduled\Cron::getScheduledTimes
     */
    public function testScheduledTimes()
    {
        $currentTime = new \DateTime();
        $currentTime->setTime($currentTime->format('H'), $currentTime->format('i'));
        $currentTime->add(new \DateInterval('PT1M')); //skip current minute

        $endTime = clone $currentTime;
        $endTime->add(new \DateInterval('PT1H'));

        $this->watcher->setInterval('PT1H');
        $this->watcher->setExpression('* ' . $currentTime->format('H') . ' ' . $currentTime->format('d') . ' * *');

        $this->assertTrue($this->watcher->isScheduled());
        $this->assertEquals([['start' => $currentTime, 'end' => $endTime]], $this->watcher->getScheduledTimes(1));

        $this->watcher->setExpression('* ' . $currentTime->format('H') . ' ' . $currentTime->format('D') . ' * *');
        $this->assertEquals([], $this->watcher->getScheduledTimes());
    }
}
