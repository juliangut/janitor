<?php

/*
 * janitor (http://juliangut.com/janitor).
 * Effortless maintenance management.
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/janitor
 * @author Julián Gutiérrez <juliangut@gmail.com>
 */

namespace Janitor\Test\Excluder;

use Janitor\Excluder\Path;
use Zend\Diactoros\ServerRequestFactory;

/**
 * Path based maintenance excluder test.
 */
class PathTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    protected $excludedPaths = [
        '/user',
        '/^\/blog\/.+/',
    ];

    /**
     * @expectedException \RuntimeException
     */
    public function testNoPath()
    {
        $excluder = new Path();

        $excluder->isExcluded(ServerRequestFactory::fromGlobals());
    }

    public function testIsExcludedByString()
    {
        $request = ServerRequestFactory::fromGlobals();
        $excluder = new Path($this->excludedPaths[0]);

        self::assertTrue($excluder->isExcluded($request->withUri($request->getUri()->withPath('/user'))));
    }

    public function testIsExcludedByRegex()
    {
        $request = ServerRequestFactory::fromGlobals();
        $excluder = new Path($this->excludedPaths);

        self::assertTrue($excluder->isExcluded($request->withUri($request->getUri()->withPath('/blog/post'))));
    }

    public function testIsNotExcluded()
    {
        $request = ServerRequestFactory::fromGlobals();
        $excluder = new Path($this->excludedPaths);

        self::assertFalse($excluder->isExcluded($request->withUri($request->getUri()->withPath('/home'))));
    }
}
