<?php
/**
 * Effortless maintenance management (http://juliangut.com/janitor)
 *
 * @link https://github.com/juliangut/janitor for the canonical source repository
 *
 * @license https://github.com/juliangut/janitor/blob/master/LICENSE
 */

namespace Janitor\Test;

use Janitor\Janitor;
use Zend\Diactoros\ServerRequestFactory;
use Zend\Diactoros\Response;

/**
 * @covers \Janitor\Janitor
 */
class JanitorTest extends \PHPUnit_Framework_TestCase
{
    protected $janitor;

    public function setUp()
    {
        $watcher = $this->getMock('Janitor\\Watcher');
        $watcher->expects($this->any())->method('isActive')->will($this->returnValue(false));

        $excluder = $this->getMock('Janitor\\Excluder');
        $excluder->expects($this->any())->method('isExcluded')->will($this->returnValue(false));

        $this->janitor = new Janitor([$watcher], [$excluder]);
    }

    /**
     * @covers \Janitor\Janitor::getScheduledTimes
     */
    public function testScheduled()
    {
        $start = new \DateTime();
        $start->add(new \DateInterval('P1D'));
        $end = clone $start;
        $end->add(new \DateInterval('PT2H'));
        $scheduledTimes = [['start' => $start, 'end' => $end]];

        $start = new \DateTime();
        $start->add(new \DateInterval('PT2H'));
        $end = clone $start;
        $end->add(new \DateInterval('PT1H'));
        array_unshift($scheduledTimes, ['start' => $start, 'end' => $end]);

        $start = new \DateTime();
        $start->add(new \DateInterval('P1D'));
        $start->add(new \DateInterval('PT1H'));
        $end = clone $start;
        $end->add(new \DateInterval('PT3H'));
        $scheduledTimes[] = ['start' => $start, 'end' => $end];
        $scheduledTimes[] = ['start' => $start, 'end' => $end];

        foreach ($scheduledTimes as $times) {
            $watcher = $this->getMock('Janitor\\ScheduledWatcher');
            $watcher->expects($this->any())->method('isActive')->will($this->returnValue(true));
            $watcher->expects($this->any())->method('isScheduled')->will($this->returnValue(true));
            $watcher->expects($this->any())->method('getScheduledTimes')->will($this->returnValue([$times]));

            $this->janitor->addWatcher($watcher);
        }

        $this->assertEquals($scheduledTimes, $this->janitor->getScheduledTimes());
    }

    /**
     * @covers \Janitor\Janitor::__invoke
     * @covers \Janitor\Janitor::getActiveWatcher
     * @covers \Janitor\Janitor::isExcluded
     */
    public function testNoActiveWatcher()
    {
        $janitor = $this->janitor;

        $response = $janitor(
            ServerRequestFactory::fromGlobals(),
            new Response('php://temp'),
            function ($request, $response) {
                return $response->withHeader('janitor', 'tested');
            }
        );
        $this->assertEquals('tested', $response->getHeaderLine('janitor'));
    }

    /**
     * @covers \Janitor\Janitor::addWatcher
     * @covers \Janitor\Janitor::__invoke
     * @covers \Janitor\Janitor::getActiveWatcher
     * @covers \Janitor\Janitor::isExcluded
     * @covers \Janitor\Janitor::getHandler
     */
    public function testDefaultHandler()
    {
        $janitor = $this->janitor;

        $watcher = $this->getMock('Janitor\\Watcher');
        $watcher->expects($this->any())->method('isActive')->will($this->returnValue(true));
        $this->janitor->addWatcher($watcher);

        $request = ServerRequestFactory::fromGlobals();
        $request = $request->withHeader('Accept', 'application/json');

        $response = $janitor(
            $request,
            new Response('php://temp'),
            function () {
            }
        );
        $this->assertTrue(substr($response->getBody(), 0, 1) === '{');
    }

    /**
     * @covers \Janitor\Janitor::addWatcher
     * @covers \Janitor\Janitor::__invoke
     * @covers \Janitor\Janitor::setHandler
     * @covers \Janitor\Janitor::getActiveWatcher
     * @covers \Janitor\Janitor::isExcluded
     * @covers \Janitor\Janitor::getHandler
     */
    public function testCustomHandler()
    {
        $janitor = $this->janitor;

        $watcher = $this->getMock('Janitor\\Watcher');
        $watcher->expects($this->any())->method('isActive')->will($this->returnValue(true));
        $this->janitor->addWatcher($watcher);

        $this->janitor->setHandler(function ($request, $response, $watcher) {
            return $response->withHeader('active_watcher', get_class($watcher));
        });

        $response = $janitor(
            ServerRequestFactory::fromGlobals(),
            new Response('php://temp'),
            function () {
            }
        );
        $this->assertEquals(get_class($watcher), $response->getHeaderLine('active_watcher'));
    }

    /**
     * @covers \Janitor\Janitor::addWatcher
     * @covers \Janitor\Janitor::addExcluder
     * @covers \Janitor\Janitor::__invoke
     * @covers \Janitor\Janitor::getActiveWatcher
     * @covers \Janitor\Janitor::isExcluded
     */
    public function testIsExcluded()
    {
        $janitor = $this->janitor;

        $watcher = $this->getMock('Janitor\\Watcher');
        $watcher->expects($this->any())->method('isActive')->will($this->returnValue(true));
        $this->janitor->addWatcher($watcher);

        $excluder = $this->getMock('Janitor\\Excluder');
        $excluder->expects($this->once())->method('isExcluded')->will($this->returnValue(true));
        $this->janitor->addExcluder($excluder);

        $response = $janitor(
            ServerRequestFactory::fromGlobals(),
            new Response('php://temp'),
            function ($request, $response) {
                return $response->withHeader('active_watcher', get_class($request->getAttribute('active_watcher')));
            }
        );
        $this->assertEquals(get_class($watcher), $response->getHeaderLine('active_watcher'));
    }
}
