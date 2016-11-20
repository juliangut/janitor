<?php

/*
 * janitor (http://juliangut.com/janitor).
 * Effortless maintenance management.
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/janitor
 * @author Julián Gutiérrez <juliangut@gmail.com>
 */

namespace Janitor\Test;

use Janitor\Excluder\ExcluderInterface;
use Janitor\Runner as Janitor;
use Janitor\Watcher\ScheduledWatcherInterface;
use Janitor\Watcher\WatcherInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequestFactory;

/**
 * Janitor runner tests.
 */
class RunnerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Janitor
     */
    protected $janitor;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $watcher = $this->getMockBuilder(WatcherInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $watcher
            ->expects(self::any())
            ->method('isActive')
            ->will(self::returnValue(false));

        $excluder = $this->getMockBuilder(ExcluderInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $excluder
            ->expects(self::any())
            ->method('isExcluded')
            ->will(self::returnValue(false));

        $this->janitor = new Janitor([$watcher], [$excluder]);
    }

    public function testScheduled()
    {
        $start = new \DateTime('now', new \DateTimeZone('UTC'));
        $start->add(new \DateInterval('P1D'));
        $end = clone $start;
        $end->add(new \DateInterval('PT2H'));
        $scheduledTimes = [['start' => $start, 'end' => $end]];

        $start = new \DateTime('now', new \DateTimeZone('UTC'));
        $start->add(new \DateInterval('PT2H'));
        $end = clone $start;
        $end->add(new \DateInterval('PT1H'));
        array_unshift($scheduledTimes, ['start' => $start, 'end' => $end]);

        $start = new \DateTime('now', new \DateTimeZone('UTC'));
        $start->add(new \DateInterval('P1D'));
        $start->add(new \DateInterval('PT1H'));
        $end = clone $start;
        $end->add(new \DateInterval('PT3H'));
        $scheduledTimes[] = ['start' => $start, 'end' => $end];
        $scheduledTimes[] = ['start' => $start, 'end' => $end];

        foreach ($scheduledTimes as $times) {
            $watcher = $this->getMockBuilder(ScheduledWatcherInterface::class)
                ->disableOriginalConstructor()
                ->getMock();
            $watcher
                ->expects(self::any())
                ->method('isActive')
                ->will(self::returnValue(true));
            $watcher
                ->expects(self::any())
                ->method('isScheduled')
                ->will(self::returnValue(true));
            $watcher
                ->expects(self::any())
                ->method('getScheduledTimes')
                ->will(self::returnValue([$times]));
            /* @var WatcherInterface $watcher */

            $this->janitor->addWatcher($watcher);
        }

        self::assertEquals($scheduledTimes, $this->janitor->getScheduledTimes());
    }

    public function testNoActiveWatcher()
    {
        $janitor = $this->janitor;

        /* @var \Psr\Http\Message\ResponseInterface $response */
        $response = $janitor(
            ServerRequestFactory::fromGlobals(),
            new Response('php://temp'),
            function (ServerRequestInterface $request, ResponseInterface $response) {
                return $response->withHeader('janitor', 'tested');
            }
        );
        self::assertEquals('tested', $response->getHeaderLine('janitor'));
    }

    public function testDefaultHandler()
    {
        $janitor = $this->janitor;

        $watcher = $this->getMockBuilder(WatcherInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $watcher
            ->expects(self::any())
            ->method('isActive')
            ->will(self::returnValue(true));
        /* @var WatcherInterface $watcher */
        $this->janitor->addWatcher($watcher);

        $request = ServerRequestFactory::fromGlobals();
        $request = $request->withHeader('Accept', 'application/json');

        /* @var \Psr\Http\Message\ResponseInterface $response */
        $response = $janitor(
            $request,
            new Response('php://temp'),
            function () {
            }
        );
        self::assertSame(strpos($response->getBody(), '{'), 0);
    }

    public function testCustomHandler()
    {
        $janitor = $this->janitor;

        $watcher = $this->getMockBuilder(WatcherInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $watcher
            ->expects(self::any())
            ->method('isActive')
            ->will(self::returnValue(true));
        /* @var WatcherInterface $watcher */
        $this->janitor->addWatcher($watcher);

        $this->janitor->setHandler(
            function (ServerRequestInterface $request, ResponseInterface $response, $watcher) {
                return $response->withHeader('active_watcher', get_class($watcher));
            }
        );

        /* @var \Psr\Http\Message\ResponseInterface $response */
        $response = $janitor(
            ServerRequestFactory::fromGlobals(),
            new Response('php://temp'),
            function () {
            }
        );
        self::assertEquals(get_class($watcher), $response->getHeaderLine('active_watcher'));
    }

    public function testIsExcluded()
    {
        $janitor = $this->janitor;

        $watcher = $this->getMockBuilder(WatcherInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $watcher
            ->expects(self::any())
            ->method('isActive')
            ->will(self::returnValue(true));
        /* @var WatcherInterface $watcher */
        $this->janitor->addWatcher($watcher);

        $excluder = $this->getMockBuilder(ExcluderInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $excluder
            ->expects(self::once())
            ->method('isExcluded')
            ->will(self::returnValue(true));
        /* @var ExcluderInterface $excluder */
        $this->janitor->addExcluder($excluder);

        $customAttributeName = 'custom';
        $this->janitor->setAttributeName($customAttributeName);
        self::assertEquals($customAttributeName, $this->janitor->getAttributeName());

        /* @var \Psr\Http\Message\ResponseInterface $response */
        $response = $janitor(
            ServerRequestFactory::fromGlobals(),
            new Response('php://temp'),
            function (ServerRequestInterface $request, ResponseInterface $response) use ($customAttributeName) {
                return $response->withHeader(
                    $customAttributeName,
                    get_class($request->getAttribute($customAttributeName))
                );
            }
        );
        self::assertEquals(get_class($watcher), $response->getHeaderLine($customAttributeName));
    }
}
