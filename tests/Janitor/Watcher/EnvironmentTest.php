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
 * Environment variable maintenance status watcher test.
 */
class EnvironmentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * {@inheritdoc}
     */
    public function tearDown()
    {
        putenv('JanitorMaintenance');
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testNoVar()
    {
        $watcher = new Environment('');

        $watcher->isActive();
    }

    public function testIsNotActive()
    {
        $watcher = new Environment('JanitorMaintenance');

        self::assertFalse($watcher->isActive());

        $watcher->addVariable('NoMaintenance', 'active');
        self::assertFalse($watcher->isActive());

        putenv('JanitorMaintenance=true');
        $watcher->addVariable('JanitorMaintenance', 'Off');
        self::assertFalse($watcher->isActive());

        putenv('JanitorMaintenance=false');
        $watcher->addVariable('JanitorMaintenance', true);
        self::assertFalse($watcher->isActive());
    }

    public function testIsActive()
    {
        $watcher = new Environment('JanitorMaintenance');

        putenv('JanitorMaintenance=true');
        self::assertTrue($watcher->isActive());

        putenv('JanitorMaintenance=On');
        $watcher->addVariable('JanitorMaintenance', 'On');
        self::assertTrue($watcher->isActive());

        putenv('JanitorMaintenance="yes"');
        $watcher->addVariable('JanitorMaintenance', 'yes');
        self::assertTrue($watcher->isActive());
    }
}
