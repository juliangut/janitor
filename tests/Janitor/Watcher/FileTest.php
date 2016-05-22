<?php
/**
 * Effortless maintenance management (http://juliangut.com/janitor)
 *
 * @link https://github.com/juliangut/janitor for the canonical source repository
 *
 * @license https://github.com/juliangut/janitor/blob/master/LICENSE
 */

namespace Janitor\Test\Watcher;

use Janitor\Watcher\File;

/**
 * Class FileTest
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
