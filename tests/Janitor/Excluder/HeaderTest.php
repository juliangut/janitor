<?php
/**
 * Effortless maintenance management (http://juliangut.com/janitor)
 *
 * @link https://github.com/juliangut/janitor for the canonical source repository
 *
 * @license https://github.com/juliangut/janitor/blob/master/LICENSE
 */

namespace Janitor\Test\Excluder;

use Janitor\Excluder\Header;
use Zend\Diactoros\ServerRequestFactory;

/**
 * @covers \Janitor\Excluder\Header
 */
class HeaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers \Janitor\Excluder\Header::isExcluded
     */
    public function testIsExcludedExists()
    {
        $request = ServerRequestFactory::fromGlobals();
        $excluder = new Header('X-Custom-Header');

        $this->assertTrue($excluder->isExcluded($request->withHeader('X-Custom-Header', '')));
    }

    /**
     * @covers \Janitor\Excluder\Header::__construct
     * @covers \Janitor\Excluder\Header::isExcluded
     */
    public function testIsExcludedByString()
    {
        $request = ServerRequestFactory::fromGlobals();
        $excluder = new Header('X-Custom-Header', 'my-value');

        $this->assertTrue($excluder->isExcluded($request->withHeader('X-Custom-Header', 'my-value')));
    }

    /**
     * @covers \Janitor\Excluder\Header::isExcluded
     */
    public function testIsExcludedByRegex()
    {
        $request = ServerRequestFactory::fromGlobals();
        $excluder = new Header('X-Custom-Header', '/^my/');

        $this->assertTrue($excluder->isExcluded($request->withHeader('X-Custom-Header', 'my-value')));
    }

    /**
     * @covers \Janitor\Excluder\Header::isExcluded
     */
    public function testIsNotExcluded()
    {
        $excluder = new Header('X-Custom-Header');

        $this->assertFalse($excluder->isExcluded(ServerRequestFactory::fromGlobals()));
    }
}
