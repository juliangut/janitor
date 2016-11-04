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

use Janitor\Watcher\Environment;

/**
 * Class EnvironmentTest.
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
        $this->watcher = new Environment('JanitorMaintenance');
    }

    public function testIsNotActive()
    {
        putenv('JanitorMaintenance=On');

        self::assertTrue($this->watcher->isActive());

        $this->watcher->addVariable('JanitorMaintenance', 'Off');

        self::assertFalse($this->watcher->isActive());

        putenv('JanitorMaintenance');
    }

    public function testIsActive()
    {
        self::assertFalse($this->watcher->isActive());

        putenv('JanitorMaintenance=On');

        $this->watcher->addVariable('JanitorMaintenance', 'On');

        self::assertTrue($this->watcher->isActive());

        putenv('JanitorMaintenance');
    }
}
