<?php
/**
 * Effortless maintenance management (http://juliangut.com/janitor)
 *
 * @link https://github.com/juliangut/janitor for the canonical source repository
 *
 * @license https://github.com/juliangut/janitor/blob/master/LICENSE
 */

namespace Janitor\Test\Excluder;

use Janitor\Excluder\Path;
use Zend\Diactoros\ServerRequestFactory;

/**
 * @covers \Janitor\Excluder\Path
 */
class PathTest extends \PHPUnit_Framework_TestCase
{
    protected $excludedPaths = [
        '/user',
        '/^\/blog\/.+/',
    ];

    /**
     * @covers \Janitor\Excluder\Path::__construct
     * @covers \Janitor\Excluder\Path::addPath
     * @covers \Janitor\Excluder\Path::isExcluded
     */
    public function testIsExcludedByString()
    {
        $request = ServerRequestFactory::fromGlobals();
        $excluder = new Path($this->excludedPaths);

        $this->assertTrue($excluder->isExcluded($request->withUri($request->getUri()->withPath('/user'))));
    }

    /**
     * @covers \Janitor\Excluder\Path::__construct
     * @covers \Janitor\Excluder\Path::addPath
     * @covers \Janitor\Excluder\Path::isExcluded
     */
    public function testIsExcludedByRegex()
    {
        $request = ServerRequestFactory::fromGlobals();
        $excluder = new Path($this->excludedPaths);

        $this->assertTrue($excluder->isExcluded($request->withUri($request->getUri()->withPath('/blog/post'))));
    }

    /**
     * @covers \Janitor\Excluder\Path::isExcluded
     */
    public function testIsNotExcluded()
    {
        $request = ServerRequestFactory::fromGlobals();
        $excluder = new Path($this->excludedPaths);

        $this->assertFalse($excluder->isExcluded($request->withUri($request->getUri()->withPath('/home'))));
    }
}
