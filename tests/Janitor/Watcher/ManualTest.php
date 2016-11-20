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

use Janitor\Watcher\Manual;

/**
 * Manual maintenance status watcher test.
 */
class ManualTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Manual
     */
    protected $watcher;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->watcher = new Manual;
    }

    public function testMutatorsAccessors()
    {
        self::assertFalse($this->watcher->isActive());

        $this->watcher->setActive(true);

        self::assertTrue($this->watcher->isActive());
    }
}
