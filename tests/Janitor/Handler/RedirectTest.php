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
use Janitor\ScheduledWatcher;
use Janitor\Watcher;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequestFactory;

/**
 * Class RedirectTest
 */
class RedirectTest extends \PHPUnit_Framework_TestCase
{
    public function testAbsouteRedirection()
    {
        $watcher = $this->getMockBuilder(Watcher::class)->disableOriginalConstructor()->getMock();
        $handler = new Redirect('http://example.com');

        /* @var \Psr\Http\Message\ResponseInterface $response */
        $response = $handler(ServerRequestFactory::fromGlobals(), new Response('php://temp'), $watcher);

        self::assertEquals(302, $response->getStatusCode());
        self::assertEquals('http://example.com', $response->getHeaderLine('Location'));
        self::assertEquals('no-cache', $response->getHeaderLine('Pragma'));
        self::assertTrue($response->hasHeader('Cache-Control'));
    }

    public function testRelativeRedirection()
    {
        $request = ServerRequestFactory::fromGlobals();
        $request = $request->withUri($request->getUri()->withHost('mydomain.com'));

        $watcher = $this->getMockBuilder(Watcher::class)->disableOriginalConstructor()->getMock();
        $handler = new Redirect('/maintenance');

        /* @var \Psr\Http\Message\ResponseInterface $response */
        $response = $handler($request, new Response('php://temp'), $watcher);

        self::assertEquals(302, $response->getStatusCode());
        self::assertEquals('http://mydomain.com/maintenance', $response->getHeaderLine('Location'));
        self::assertEquals('no-cache', $response->getHeaderLine('Pragma'));
        self::assertTrue($response->hasHeader('Cache-Control'));
    }

    public function testRedirectScheduled()
    {
        $watcher = $this->getMockBuilder(ScheduledWatcher::class)->disableOriginalConstructor()->getMock();
        $watcher->expects(self::once())->method('getEnd')->will(self::returnValue(new \DateTime));
        $handler = new Redirect('http://example.com');

        /* @var \Psr\Http\Message\ResponseInterface $response */
        $response = $handler(ServerRequestFactory::fromGlobals(), new Response('php://temp'), $watcher);

        self::assertEquals(302, $response->getStatusCode());
        self::assertTrue($response->hasHeader('Expires'));
    }
}
