<?php
/**
 * Effortless maintenance management (http://juliangut.com/janitor)
 *
 * @link https://github.com/juliangut/janitor for the canonical source repository
 *
 * @license https://github.com/juliangut/janitor/blob/master/LICENSE
 */

namespace Janitor\Test\Watcher;

use Janitor\Watcher\Environment;

/**
 * Class EnvironmentTest
 */
class EnvironmentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Environment
     */
    protected $watcher;

    /**
     * {@inheritdoc}
     */
    public static function setUpBeforeClass()
    {
        putenv('JanitorMaintenance=On');
    }

    /**
     * {@inheritdoc}
     */
    public static function tearDownAfterClass()
    {
        putenv('JanitorMaintenance');
    }

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->watcher = new Environment('JanitorMaintenance', 'Ok');
    }

    public function testMutatorsAccessors()
    {
        $this->watcher->setVar('JanitorMaintenance');
        $this->watcher->setValue('On');

        self::assertEquals('JanitorMaintenance', $this->watcher->getVar());
        self::assertEquals('On', $this->watcher->getValue());
    }

    public function testIsNotActive()
    {
        self::assertFalse($this->watcher->isActive());

        $this->watcher->setVar('ficticious-environment-variable');

        self::assertFalse($this->watcher->isActive());

        $this->watcher->setVar('JanitorMaintenance');
        $this->watcher->setValue('Off');

        self::assertFalse($this->watcher->isActive());
    }

    public function testIsActive()
    {
        self::assertFalse($this->watcher->isActive());

        $this->watcher->setValue('On');

        self::assertTrue($this->watcher->isActive());
    }
}
