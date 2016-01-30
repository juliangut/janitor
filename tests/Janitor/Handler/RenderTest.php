<?php
/**
 * Effortless maintenance management (http://juliangut.com/janitor)
 *
 * @link https://github.com/juliangut/janitor for the canonical source repository
 *
 * @license https://github.com/juliangut/janitor/blob/master/LICENSE
 */

namespace Janitor\Test\Handler;

use Janitor\Handler\Render;
use Zend\Diactoros\ServerRequestFactory;
use Zend\Diactoros\Response;

/**
 * @covers \Janitor\Handler\Render
 */
class RenderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers \Janitor\Handler\Render::__invoke
     */
    public function testStatus()
    {
        $watcher = $this->getMock('Janitor\\Watcher');
        $watcher->expects($this->once())->method('isActive')->will($this->returnValue(false));
        $handler = new Render;

        $response = $handler(ServerRequestFactory::fromGlobals(), new Response('php://temp'), $watcher);

        $this->assertEquals(503, $response->getStatusCode());
        $this->assertEquals('no-cache', $response->getHeaderLine('Pragma'));
        $this->assertTrue($response->hasHeader('Cache-Control'));
    }

    /**
     * @covers \Janitor\Handler\Render::__invoke
     */
    public function testRenderingNotActiveHtml()
    {
        $watcher = $this->getMock('Janitor\\Watcher');
        $watcher->expects($this->once())->method('isActive')->will($this->returnValue(false));
        $handler = new Render;

        $response = $handler(ServerRequestFactory::fromGlobals(), new Response('php://temp'), $watcher);

        $this->assertEquals('text/html', $response->getHeaderLine('Content-Type'));
        $this->assertEquals('<html><head>', substr($response->getBody(), 0, 12));
        $this->assertNotFalse(strpos($response->getBody(), 'Maintenance mode is not active!'));
    }

    /**
     * @covers \Janitor\Handler\Render::__invoke
     */
    public function testRenderingActiveHtml()
    {
        $watcher = $this->getMock('Janitor\\Watcher');
        $watcher->expects($this->once())->method('isActive')->will($this->returnValue(true));
        $handler = new Render;

        $request = ServerRequestFactory::fromGlobals();
        $request = $request->withHeader('Accept', 'text/html');

        $response = $handler($request, new Response('php://temp'), $watcher);

        $this->assertEquals('text/html', $response->getHeaderLine('Content-Type'));
        $this->assertEquals('<html><head>', substr($response->getBody(), 0, 12));
        $this->assertNotFalse(strpos($response->getBody(), 'Undergoing maintenance tasks'));
    }

    /**
     * @covers \Janitor\Handler\Render::__invoke
     */
    public function testRenderingActiveJson()
    {
        $watcher = $this->getMock('Janitor\\Watcher');
        $watcher->expects($this->once())->method('isActive')->will($this->returnValue(true));
        $handler = new Render;

        $request = ServerRequestFactory::fromGlobals();
        $request = $request->withHeader('Accept', 'application/json');

        $response = $handler($request, new Response('php://temp'), $watcher);

        $this->assertEquals('application/json', $response->getHeaderLine('Content-Type'));
        $this->assertEquals(
            '{    "message": "Undergoing maintenance tasks"}',
            str_replace("\n", '', $response->getBody())
        );
    }

    /**
     * @covers \Janitor\Handler\Render::__invoke
     */
    public function testRenderingActiveXml()
    {
        $watcher = $this->getMock('Janitor\\Watcher');
        $watcher->expects($this->once())->method('isActive')->will($this->returnValue(true));
        $handler = new Render;

        $request = ServerRequestFactory::fromGlobals();
        $request = $request->withHeader('Accept', 'application/xml');

        $response = $handler($request, new Response('php://temp'), $watcher);

        $this->assertEquals('application/xml', $response->getHeaderLine('Content-Type'));
        $this->assertEquals(
            '<maintenance>  <message>Undergoing maintenance tasks</message></maintenance>',
            str_replace("\n", '', $response->getBody())
        );
    }

    /**
     * @covers \Janitor\Handler\Render::__invoke
     */
    public function testRenderingScheduled()
    {
        $watcher = $this->getMock('Janitor\\ScheduledWatcher');
        $watcher->expects($this->once())->method('isActive')->will($this->returnValue(true));
        $watcher->expects($this->any())->method('getEnd')->will($this->returnValue(new \DateTime));
        $handler = new Render;

        $response = $handler(ServerRequestFactory::fromGlobals(), new Response('php://temp'), $watcher);

        $this->assertTrue($response->hasHeader('Expires'));
        $this->assertNotFalse(strpos($response->getBody(), 'Undergoing maintenance tasks until'));
    }
}
