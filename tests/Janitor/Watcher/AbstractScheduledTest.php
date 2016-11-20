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

use Janitor\Watcher\AbstractScheduled;

/**
 * Class AbstractScheduledTest.
 */
class AbstractScheduledTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AbstractScheduled
     */
    protected $watcher;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->watcher = $this->getMockForAbstractClass(AbstractScheduled::class);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testBadTimeZoneName()
    {
        $this->watcher->setTimeZone('World/Unknown');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testBadTimeZoneOffset()
    {
        $this->watcher->setTimeZone(158622);
    }

    public function testTimeZone()
    {
        self::assertEquals(
            (new \DateTimeZone(date_default_timezone_get()))->getName(),
            $this->watcher->getTimeZone()->getName()
        );

        $this->watcher->setTimeZone(1);
        self::assertEquals('Europe/London', $this->watcher->getTimeZone()->getName());

        $this->watcher->setTimeZone('Europe/Madrid');
        self::assertEquals('Europe/Madrid', $this->watcher->getTimeZone()->getName());
    }
}
