<?php
/**
 * Effortless maintenance management (http://juliangut.com/janitor)
 *
 * @link https://github.com/juliangut/janitor for the canonical source repository
 *
 * @license https://github.com/juliangut/janitor/blob/master/LICENSE
 */

namespace Janitor\Test\Handler;

use Janitor\Handler\Redirect;
use Zend\Diactoros\ServerRequestFactory;
use Zend\Diactoros\Response;

/**
 * @covers \Janitor\Handler\Redirect
 */
class RedirectTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers \Janitor\Handler\Redirect::__construct
     * @covers \Janitor\Handler\Redirect::__invoke
     */
    public function testRendering()
    {
        $watcher = $this->getMock('Janitor\\Watcher');
        $handler = new Redirect('http://example.com');

        $response = $handler(ServerRequestFactory::fromGlobals(), new Response('php://temp'), $watcher);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('http://example.com', $response->getHeaderLine('Location'));
        $this->assertEquals('no-cache', $response->getHeaderLine('Pragma'));
        $this->assertTrue($response->hasHeader('Cache-Control'));
    }

    /**
     * @covers \Janitor\Handler\Redirect::__construct
     * @covers \Janitor\Handler\Redirect::__invoke
     */
    public function testRenderingScheduled()
    {
        $watcher = $this->getMock('Janitor\\ScheduledWatcher');
        $watcher->expects($this->once())->method('getEnd')->will($this->returnValue(new \DateTime));
        $handler = new Redirect('http://example.com');

        $response = $handler(ServerRequestFactory::fromGlobals(), new Response('php://temp'), $watcher);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertTrue($response->hasHeader('Expires'));
    }
}
