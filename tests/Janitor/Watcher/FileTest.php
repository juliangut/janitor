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

use Janitor\Watcher\File;

/**
 * File existence maintenance status watcher test.
 */
class FileTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var File
     */
    protected $watcher;

    /**
     * @var string
     */
    protected static $tmpFile;

    /**
     * {@inheritdoc}
     */
    public static function setUpBeforeClass()
    {
        self::$tmpFile = sys_get_temp_dir() . '/maintenance';

        touch(self::$tmpFile);
    }

    /**
     * {@inheritdoc}
     */
    public static function tearDownAfterClass()
    {
        unlink(self::$tmpFile);
    }

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->watcher = new File(self::$tmpFile);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testNoFiles()
    {
        $watcher = new File();

        $watcher->isActive();
    }

    public function testIsActive()
    {
        self::assertTrue($this->watcher->isActive());
    }

    public function testIsNotActive()
    {
        unlink(self::$tmpFile);

        self::assertFalse($this->watcher->isActive());

        touch(self::$tmpFile);
    }
}
