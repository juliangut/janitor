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

use Janitor\Excluder\Header;
use Zend\Diactoros\ServerRequestFactory;

/**
 * Class HeaderTest.
 */
class HeaderTest extends \PHPUnit_Framework_TestCase
{
    public function testIsExcludedExists()
    {
        $request = ServerRequestFactory::fromGlobals();
        $excluder = new Header('X-Custom-Header');

        self::assertTrue($excluder->isExcluded($request->withHeader('X-Custom-Header', '')));
    }

    public function testIsExcludedByString()
    {
        $request = ServerRequestFactory::fromGlobals();
        $excluder = new Header('X-Custom-Header', 'my-value');

        self::assertTrue($excluder->isExcluded($request->withHeader('X-Custom-Header', 'my-value')));
    }

    public function testIsExcludedByRegex()
    {
        $request = ServerRequestFactory::fromGlobals();
        $excluder = new Header('X-Custom-Header', '/^my/');

        self::assertTrue($excluder->isExcluded($request->withHeader('X-Custom-Header', 'my-value')));
    }

    public function testIsNotExcluded()
    {
        $excluder = new Header('X-Custom-Header');

        self::assertFalse($excluder->isExcluded(ServerRequestFactory::fromGlobals()));
    }
}
