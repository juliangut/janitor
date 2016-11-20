<?php

/*
 * janitor (http://juliangut.com/janitor).
 * Effortless maintenance management.
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/janitor
 * @author Julián Gutiérrez <juliangut@gmail.com>
 */

namespace Janitor\Test\Handler;

use Janitor\Handler\Redirect;
use Janitor\Watcher\ScheduledWatcherInterface;
use Janitor\Watcher\WatcherInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequestFactory;

/**
 * Redirect maintenance handler test.
 */
class RedirectTest extends \PHPUnit_Framework_TestCase
{
    public function testAbsoluteRedirection()
    {
        $watcher = $this->getMockBuilder(WatcherInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
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

        $watcher = $this->getMockBuilder(WatcherInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
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
        $watcher = $this->getMockBuilder(ScheduledWatcherInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $watcher
            ->expects(self::once())
            ->method('getEnd')
            ->will(self::returnValue(new \DateTime));
        $handler = new Redirect('http://example.com');

        /* @var \Psr\Http\Message\ResponseInterface $response */
        $response = $handler(ServerRequestFactory::fromGlobals(), new Response('php://temp'), $watcher);

        self::assertEquals(302, $response->getStatusCode());
        self::assertTrue($response->hasHeader('Expires'));
    }
}
