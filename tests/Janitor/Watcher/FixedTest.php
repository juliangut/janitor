<?php

/*
 * janitor (http://juliangut.com/janitor).
 * Effortless maintenance management.
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/janitor
 * @author Julián Gutiérrez <juliangut@gmail.com>
 */

namespace Janitor\Test\Watcher;

use Janitor\Watcher\Fixed;

/**
 * Fixed date scheduled maintenance status watcher test.
 */
class FixedTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Fixed
     */
    protected $watcher;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->watcher = new Fixed('yesterday', 'tomorrow', 'UTC');
    }

    public function testDefaults()
    {
        self::assertTrue($this->watcher->isActive());
        self::assertFalse($this->watcher->isScheduled());
        self::assertEquals([], $this->watcher->getScheduledTimes());
    }

    public function testMutatorsAccessors()
    {
        $start = new \DateTime('now', new \DateTimeZone('UTC'));
        $end = clone $start;
        $end->add(new \DateInterval('PT1H'));

        $this->watcher->setStart($start);
        self::assertEquals($start, $this->watcher->getStart());
        $this->watcher->setEnd($end);
        self::assertEquals($end, $this->watcher->getEnd());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidStartDate()
    {
        $this->watcher->setEnd('now');

        $start = new \DateTime('now', new \DateTimeZone('UTC'));
        $start->add(new \DateInterval('P10D'));
        $this->watcher->setStart($start);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidStartString()
    {
        $this->watcher->setStart('wow');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidEndDate()
    {
        $this->watcher->setStart('now');

        $end = new \DateTime('now', new \DateTimeZone('UTC'));
        $end->sub(new \DateInterval('P10D'));
        $this->watcher->setEnd($end);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidEndString()
    {
        $this->watcher->setEnd('wow');
    }

    public function testScheduledTime()
    {
        self::assertEquals([], $this->watcher->getScheduledTimes());

        $start = new \DateTime('now', new \DateTimeZone('UTC'));
        $start->add(new \DateInterval('P1D'));
        $end = clone $start;
        $this->watcher->setEnd($end);
        $this->watcher->setStart($start);
        self::assertEquals([['start' => $start, 'end' => $end]], $this->watcher->getScheduledTimes());

        $start = new \DateTime('now', new \DateTimeZone('UTC'));
        $start->sub(new \DateInterval('P1D'));
        $end = clone $start;
        $this->watcher->setStart($start);
        $this->watcher->setEnd($end);
        self::assertEquals([], $this->watcher->getScheduledTimes());
    }

    public function testBeforeTime()
    {
        $start = new \DateTime('now', new \DateTimeZone('UTC'));
        $start->add(new \DateInterval('P1D'));
        $end = clone $start;
        $watcher = new Fixed($start, $end);

        self::assertFalse($watcher->isActive());
        self::assertTrue($watcher->isScheduled());
    }

    public function testAfterTime()
    {
        $start = new \DateTime('now', new \DateTimeZone('UTC'));
        $start->sub(new \DateInterval('P1D'));
        $end = clone $start;
        $watcher = new Fixed($start, $end);

        self::assertFalse($watcher->isActive());
        self::assertFalse($watcher->isScheduled());
    }
}
