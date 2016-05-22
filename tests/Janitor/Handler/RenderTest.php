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
use Janitor\ScheduledWatcher;
use Janitor\Watcher;
use Zend\Diactoros\ServerRequestFactory;
use Zend\Diactoros\Response;

/**
 * Class RenderTest
 */
class RenderTest extends \PHPUnit_Framework_TestCase
{
    public function testStatus()
    {
        $watcher = $this->getMockBuilder(Watcher::class)->disableOriginalConstructor()->getMock();
        $watcher->expects(self::once())->method('isActive')->will(self::returnValue(false));
        $handler = new Render;

        /** @var \Psr\Http\Message\ResponseInterface $response */
        $response = $handler(ServerRequestFactory::fromGlobals(), new Response('php://temp'), $watcher);

        self::assertEquals(503, $response->getStatusCode());
        self::assertEquals('no-cache', $response->getHeaderLine('Pragma'));
        self::assertTrue($response->hasHeader('Cache-Control'));
    }

    public function testRenderingNotActiveHtml()
    {
        $watcher = $this->getMockBuilder(Watcher::class)->disableOriginalConstructor()->getMock();
        $watcher->expects(self::once())->method('isActive')->will(self::returnValue(false));
        $handler = new Render;

        /** @var \Psr\Http\Message\ResponseInterface $response */
        $response = $handler(ServerRequestFactory::fromGlobals(), new Response('php://temp'), $watcher);

        self::assertEquals('text/html', $response->getHeaderLine('Content-Type'));
        self::assertEquals('<html><head>', substr($response->getBody(), 0, 12));
        self::assertNotFalse(strpos($response->getBody(), 'Maintenance mode is not active!'));
    }

    public function testRenderingActiveHtml()
    {
        $watcher = $this->getMockBuilder(Watcher::class)->disableOriginalConstructor()->getMock();
        $watcher->expects(self::once())->method('isActive')->will(self::returnValue(true));
        $handler = new Render;

        /** @var \Psr\Http\Message\ResponseInterface $response */
        $request = ServerRequestFactory::fromGlobals();
        $request = $request->withHeader('Accept', 'text/html');

        $response = $handler($request, new Response('php://temp'), $watcher);

        self::assertEquals('text/html', $response->getHeaderLine('Content-Type'));
        self::assertEquals('<html><head>', substr($response->getBody(), 0, 12));
        self::assertNotFalse(strpos($response->getBody(), 'Undergoing maintenance tasks'));
    }

    public function testRenderingActiveJson()
    {
        $watcher = $this->getMockBuilder(Watcher::class)->disableOriginalConstructor()->getMock();
        $watcher->expects(self::once())->method('isActive')->will(self::returnValue(true));
        $handler = new Render;

        /** @var \Psr\Http\Message\ResponseInterface $response */
        $request = ServerRequestFactory::fromGlobals();
        $request = $request->withHeader('Accept', 'application/json');

        $response = $handler($request, new Response('php://temp'), $watcher);

        self::assertEquals('application/json', $response->getHeaderLine('Content-Type'));
        self::assertEquals(
            '{"message":"Undergoing maintenance tasks"}',
            (string) $response->getBody()
        );
    }

    public function testRenderingActiveXml()
    {
        $watcher = $this->getMockBuilder(Watcher::class)->disableOriginalConstructor()->getMock();
        $watcher->expects(self::once())->method('isActive')->will(self::returnValue(true));
        $handler = new Render;

        /** @var \Psr\Http\Message\ResponseInterface $response */
        $request = ServerRequestFactory::fromGlobals();
        $request = $request->withHeader('Accept', 'application/xml');

        $response = $handler($request, new Response('php://temp'), $watcher);

        self::assertEquals('application/xml', $response->getHeaderLine('Content-Type'));
        self::assertEquals(
            '<maintenance><message>Undergoing maintenance tasks</message></maintenance>',
            (string) $response->getBody()
        );
    }

    public function testRenderingScheduled()
    {
        $watcher = $this->getMockBuilder(ScheduledWatcher::class)->disableOriginalConstructor()->getMock();
        $watcher->expects(self::once())->method('isActive')->will(self::returnValue(true));
        $watcher->expects(self::any())->method('getEnd')->will(self::returnValue(new \DateTime));
        $handler = new Render;

        /** @var \Psr\Http\Message\ResponseInterface $response */
        $response = $handler(ServerRequestFactory::fromGlobals(), new Response('php://temp'), $watcher);

        self::assertTrue($response->hasHeader('Expires'));
        self::assertNotFalse(strpos($response->getBody(), 'Undergoing maintenance tasks until'));
    }
}
