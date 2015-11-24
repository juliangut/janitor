<?php
/**
 * Effortless maintenance management (http://juliangut.com/janitor)
 *
 * @link https://github.com/juliangut/janitor for the canonical source repository
 * @license https://github.com/juliangut/janitor/blob/master/LICENSE
 */

namespace Janitor\Test\Provider\Path;

use Janitor\Provider\Path\Basic;

/**
 * @covers \Janitor\Provider\Path\Basic
 */
class BasicTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers \Janitor\Provider\Path\Basic::getPath
     */
    public function testPathExtraction()
    {
        $_SERVER['SCRIPT_NAME'] = '/index.php';

        $_SERVER['REQUEST_URI'] = 'http://test.com/index.php/blog/post';
        $this->assertEquals('blog/post', (new Basic)->getPath());

        $_SERVER['REQUEST_URI'] = 'http://test.com/blog/post';
        $this->assertEquals('blog/post', (new Basic)->getPath());

        $_SERVER['SCRIPT_NAME'] = '/page/index.php';

        $_SERVER['REQUEST_URI'] = 'http://test.com/page/index.php/blog/post';
        $this->assertEquals('blog/post', (new Basic)->getPath());

        $_SERVER['REQUEST_URI'] = 'http://test.com/page/blog/post/5/';
        $this->assertEquals('blog/post/5', (new Basic)->getPath());
    }
}
