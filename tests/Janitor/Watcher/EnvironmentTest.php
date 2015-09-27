<?php
/**
 * Effortless maintenance management (http://juliangut.com/janitor)
 *
 * @link https://github.com/juliangut/janitor for the canonical source repository
 * @license https://github.com/juliangut/janitor/blob/master/LICENSE
 */

namespace Janitor\Test\Watcher;

use Janitor\Watcher\Environment;

/**
 * @covers Janitor\Watcher\Environment
 */
class EnvironmentTest extends \PHPUnit_Framework_TestCase
{
    protected $watcher;

    public static function setUpBeforeClass()
    {
        putenv('JanitorMaintenance=On');
    }

    public static function tearDownAfterClass()
    {
        putenv('JanitorMaintenance');
    }

    public function setUp()
    {
        $this->watcher = new Environment('JanitorMaintenance', 'Ok');
    }

    /**
     * @covers Janitor\Watcher\Environment::setVar
     * @covers Janitor\Watcher\Environment::getVar
     * @covers Janitor\Watcher\Environment::setValue
     * @covers Janitor\Watcher\Environment::getValue
     */
    public function testMutatorsAccessors()
    {
        $this->watcher->setVar('JanitorMaintenance');
        $this->watcher->setValue('On');

        $this->assertEquals('JanitorMaintenance', $this->watcher->getVar());
        $this->assertEquals('On', $this->watcher->getValue());
    }

    /**
     * @covers Janitor\Watcher\Environment::setVar
     * @covers Janitor\Watcher\Environment::setValue
     * @covers Janitor\Watcher\Environment::isActive
     */
    public function testIsNotActive()
    {
        $this->assertFalse($this->watcher->isActive());

        $this->watcher->setVar('ficticious-environment-variable');

        $this->assertFalse($this->watcher->isActive());

        $this->watcher->setVar('JanitorMaintenance');
        $this->watcher->setValue('Off');

        $this->assertFalse($this->watcher->isActive());
    }

    /**
     * @covers Janitor\Watcher\Environment::setVar
     * @covers Janitor\Watcher\Environment::setValue
     * @covers Janitor\Watcher\Environment::isActive
     */
    public function testIsActive()
    {
        $this->assertFalse($this->watcher->isActive());

        $this->watcher->setValue('On');

        $this->assertTrue($this->watcher->isActive());
    }
}
