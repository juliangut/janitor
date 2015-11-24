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
class ScheduledTraitTest extends \PHPUnit_Framework_TestCase
{
    protected $watcher;

    public function setUp()
    {
        $this->watcher = new Fixed('yesterday', 'tomorrow');
    }

    /**
     * @covers \Janitor\Watcher\Scheduled\ScheduledTrait::setTimeZone
     * @expectedException \Janitor\Exception\ScheduledException
     */
    public function testBadTimeZone()
    {
        $this->watcher->setTimeZone('unknown');
    }

    /**
     * @covers \Janitor\Watcher\Scheduled\ScheduledTrait::setTimeZone
     * @covers \Janitor\Watcher\Scheduled\ScheduledTrait::getTimeZone
     */
    public function testTimeZone()
    {
        $this->assertEquals(
            (new \DateTimeZone(date_default_timezone_get()))->getName(),
            $this->watcher->getTimeZone()->getName()
        );

        $this->watcher->setTimeZone('Europe/Madrid');
        $this->assertEquals('Europe/Madrid', $this->watcher->getTimeZone()->getName());
    }
}
