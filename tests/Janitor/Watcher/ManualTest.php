<?php
/**
 * Effortless maintenance management (http://juliangut.com/janitor)
 *
 * @link https://github.com/juliangut/janitor for the canonical source repository
 *
 * @license https://github.com/juliangut/janitor/blob/master/LICENSE
 */

namespace Janitor\Test\Watcher;

use Janitor\Watcher\Manual;

/**
 * Class ManualTest
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
