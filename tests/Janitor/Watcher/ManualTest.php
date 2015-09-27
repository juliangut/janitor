<?php
/**
 * Effortless maintenance management (http://juliangut.com/janitor)
 *
 * @link https://github.com/juliangut/janitor for the canonical source repository
 * @license https://github.com/juliangut/janitor/blob/master/LICENSE
 */

namespace Janitor\Test\Watcher;

use Janitor\Watcher\Manual;

/**
 * @covers Janitor\Watcher\Manual
 */
class ManualTest extends \PHPUnit_Framework_TestCase
{
    protected $watcher;

    public function setUp()
    {
        $this->watcher = new Manual;
    }

    /**
     * @covers Janitor\Watcher\Manual::setActive
     * @covers Janitor\Watcher\Manual::isActive
     */
    public function testMutatorsAccessors()
    {
        $this->assertFalse($this->watcher->isActive());

        $this->watcher->setActive(true);

        $this->assertTrue($this->watcher->isActive());
    }
}
