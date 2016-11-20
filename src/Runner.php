<?php

/*
 * janitor (http://juliangut.com/janitor).
 * Effortless maintenance management.
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/janitor
 * @author Julián Gutiérrez <juliangut@gmail.com>
 */

namespace Janitor;

use Janitor\Excluder\ExcluderInterface;
use Janitor\Handler\Render as RenderHandler;
use Janitor\Watcher\ScheduledWatcherInterface;
use Janitor\Watcher\WatcherInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Janitor runner.
 */
class Runner
{
    /**
     * List of watchers.
     *
     * @var array
     */
    protected $watchers = [];

    /**
     * List of excluders.
     *
     * @var array
     */
    protected $excluders = [];

    /**
     * Resolve handler.
     *
     * @var callable
     */
    protected $handler;

    /**
     * Request attribute name to store currently active watcher.
     *
     * @var string
     */
    protected $attributeName;

    /**
     * Runner constructor.
     *
     * @param array    $watchers
     * @param array    $excluders
     * @param callable $handler
     * @param string   $attributeName
     */
    public function __construct(
        array $watchers = [],
        array $excluders = [],
        callable $handler = null,
        $attributeName = 'active_watcher'
    ) {
        foreach ($watchers as $watcher) {
            $this->addWatcher($watcher);
        }

        foreach ($excluders as $excluder) {
            $this->addExcluder($excluder);
        }

        $this->handler = $handler;
        $this->attributeName = $attributeName;
    }

    /**
     * Add maintenance watcher.
     *
     * @param WatcherInterface $watcher
     *
     * @return $this
     */
    public function addWatcher(WatcherInterface $watcher)
    {
        $this->watchers[] = $watcher;

        return $this;
    }

    /**
     * Add excluder condition.
     *
     * @param ExcluderInterface $excluder
     *
     * @return $this
     */
    public function addExcluder(ExcluderInterface $excluder)
    {
        $this->excluders[] = $excluder;

        return $this;
    }

    /**
     * Set handler.
     *
     * @param callable $handler
     *
     * @return $this
     */
    public function setHandler(callable $handler)
    {
        $this->handler = $handler;

        return $this;
    }

    /**
     * Set request attribute name to store active watcher.
     *
     * @param string $attributeName
     *
     * @return $this
     */
    public function setAttributeName($attributeName)
    {
        $this->attributeName = $attributeName;

        return $this;
    }

    /**
     * Retrieve request attribute name storing active watcher.
     *
     * @return string
     */
    public function getAttributeName()
    {
        return $this->attributeName;
    }

    /**
     * Get next scheduled time spans.
     *
     * Returns an array of ['start' => \DateTime, 'end' => \DateTime]
     *
     * @param int $count
     *
     * @return array
     */
    public function getScheduledTimes($count = 5)
    {
        $scheduledTimes = [];

        foreach ($this->watchers as $watcher) {
            if ($watcher instanceof ScheduledWatcherInterface && $watcher->isScheduled()) {
                $scheduledTimes = array_merge($scheduledTimes, $watcher->getScheduledTimes($count));
            }
        }

        usort(
            $scheduledTimes,
            function ($time1, $time2) {
                if ($time1['start'] === $time2['start']) {
                    return 0;
                }

                return $time1['start'] < $time2['start'] ? -1 : 1;
            }
        );

        return array_slice($scheduledTimes, 0, $count);
    }

    /**
     * Run middleware.
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface      $response
     * @param callable               $next
     *
     * @return ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next)
    {
        $activeWatcher = $this->getActiveWatcher();

        if ($activeWatcher !== null) {
            if (!$this->isExcluded($request)) {
                return call_user_func($this->getHandler(), $request, $response, $activeWatcher);
            }

            $request = $request->withAttribute($this->getAttributeName(), $activeWatcher);
        }

        return $next($request, $response);
    }

    /**
     * Get currently active watcher.
     *
     * @return WatcherInterface
     */
    protected function getActiveWatcher()
    {
        foreach ($this->watchers as $watcher) {
            if ($watcher->isActive()) {
                return $watcher;
            }
        }
    }

    /**
     * Whether excluding conditions are met.
     *
     * @param ServerRequestInterface $request
     *
     * @return bool
     */
    protected function isExcluded(ServerRequestInterface $request)
    {
        foreach ($this->excluders as $excluder) {
            if ($excluder->isExcluded($request)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Retrieve handler.
     *
     * @return callable
     */
    protected function getHandler()
    {
        if (!is_callable($this->handler)) {
            $this->handler = new RenderHandler;
        }

        return $this->handler;
    }
}
