<?php
/**
 * Effortless maintenance management (http://juliangut.com/janitor)
 *
 * @link https://github.com/juliangut/janitor for the canonical source repository
 * @license https://github.com/juliangut/janitor/blob/master/LICENSE
 */

namespace Janitor\Test\Watcher;

use Janitor\Watcher\File;

/**
 * @covers Janitor\Watcher\File
 */
class FileTest extends \PHPUnit_Framework_TestCase
{
    protected $watcher;

    protected static $tmpFile;

    public static function setUpBeforeClass()
    {
        self::$tmpFile = sys_get_temp_dir() . '/maintenance';

        touch(self::$tmpFile);
    }

    public static function tearDownAfterClass()
    {
        unlink(self::$tmpFile);
    }

    public function setUp()
    {
        $this->watcher = new File(self::$tmpFile);
    }

    /**
     * @covers Janitor\Watcher\File::getFile
     */
    public function testAccessors()
    {
        $this->assertEquals(self::$tmpFile, $this->watcher->getFile());
    }

    /**
     * @covers Janitor\Watcher\File::setFile
     * @covers Janitor\Watcher\File::isActive
     */
    public function testIsActive()
    {
        $this->assertTrue($this->watcher->isActive());
    }

    /**
     * @covers Janitor\Watcher\File::setFile
     * @covers Janitor\Watcher\File::isActive
     */
    public function testIsNotActive()
    {
        $this->watcher->setFile('fakeFile');

        $this->assertFalse($this->watcher->isActive());
    }
}
