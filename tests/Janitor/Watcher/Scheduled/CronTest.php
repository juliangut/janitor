<?php

/*
 * janitor (http://juliangut.com/janitor).
 * Effortless maintenance management.
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/janitor
 * @author Julián Gutiérrez <juliangut@gmail.com>
 */

namespace Janitor\Test\Watcher\Scheduled;

use Janitor\Watcher\Scheduled\Cron;

/**
 * Class CronTest.
 */
class CronTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Cron
     */
    protected $watcher;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->watcher = new Cron('0 0 1 1 *', 'PT1M', 'UTC');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testExpressionMutatorsAccessors()
    {
        $this->watcher->setExpression(Cron::PERIOD_ANNUALLY);
        self::assertEquals('0 0 1 1 *', $this->watcher->getExpression());

        $this->watcher->setExpression('invalidExpression');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testIntervalMutatorsAccessors()
    {
        $this->watcher->setExpression(Cron::PERIOD_YEARLY);

        self::assertNull($this->watcher->getStart());
        self::assertNull($this->watcher->getEnd());

        $this->watcher->setInterval('P1Y');

        $start = new \DateTime('now', new \DateTimeZone('UTC'));
        $start->setDate($start->format('Y'), 1, 1);
        $end = clone $start;
        $end->add(new \DateInterval('P1Y'));

        self::assertEquals($start->format('Y m d'), $this->watcher->getStart()->format('Y m d'));
        self::assertEquals($end->format('Y m d'), $this->watcher->getEnd()->format('Y m d'));

        self::assertEquals(new \DateInterval('P1Y'), $this->watcher->getInterval());
        $this->watcher->setInterval('invalidDateInterval');
    }

    public function testIsNotActive()
    {
        $time   = new \DateTime('now', new \DateTimeZone('UTC'));
        $minute = $time->format('i');
        $hour   = $time->format('H');
        $day    = $time->format('d');
        $month  = $time->format('m');

        $this->watcher->setInterval('PT10S');

        $this->watcher->setExpression(sprintf('%s * * * *', $minute - 10 > -1 ? $minute - 10 : 50));
        self::assertFalse($this->watcher->isActive());

        $this->watcher->setExpression(sprintf('* %s * * *', $hour - 1 > -1 ? $hour - 1 : 23));
        self::assertFalse($this->watcher->isActive());

        $this->watcher->setExpression(sprintf('* * %s * *', $day - 1 > -1 ? $day - 1 : 28));
        self::assertFalse($this->watcher->isActive());

        $this->watcher->setExpression(sprintf('* * * %s *', $month - 1 > -1 ? $month - 1 : 12));
        self::assertFalse($this->watcher->isActive());
    }

    public function testIsActive()
    {
        $time = new \DateTime('now', new \DateTimeZone('UTC'));
        $minute = $time->format('i');
        $hour   = $time->format('H');
        $day    = $time->format('d');
        $month  = $time->format('m');

        $this->watcher->setInterval('PT1M');
        $this->watcher->setExpression(sprintf('%s * * * *', $minute));
        self::assertTrue($this->watcher->isActive());

        $this->watcher->setInterval('PT1H');
        $this->watcher->setExpression(sprintf('* %s * * *', $hour));
        self::assertTrue($this->watcher->isActive());

        $this->watcher->setInterval('P1D');
        $this->watcher->setExpression(sprintf('* * %s * *', $day));
        self::assertTrue($this->watcher->isActive());

        $this->watcher->setInterval('P1M');
        $this->watcher->setExpression(sprintf('* * * %s *', $month));
        self::assertTrue($this->watcher->isActive());
    }

    public function testScheduledTimes()
    {
        $currentTime = new \DateTime('now', new \DateTimeZone('UTC'));
        $currentTime->setTime($currentTime->format('H'), $currentTime->format('i'));
        $currentTime->add(new \DateInterval('PT1M')); //skip current minute

        $endTime = clone $currentTime;
        $endTime->add(new \DateInterval('PT1H'));

        $this->watcher->setInterval('PT1H');
        $this->watcher->setExpression('* ' . $currentTime->format('H') . ' ' . $currentTime->format('d') . ' * *');

        self::assertTrue($this->watcher->isScheduled());
        self::assertEquals([['start' => $currentTime, 'end' => $endTime]], $this->watcher->getScheduledTimes(1));

        $this->watcher->setExpression('* ' . $currentTime->format('H') . ' ' . $currentTime->format('D') . ' * *');
        self::assertEquals([], $this->watcher->getScheduledTimes());
    }
}
