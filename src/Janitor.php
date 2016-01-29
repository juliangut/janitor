<?php
/**
 * Effortless maintenance management (http://juliangut.com/janitor)
 *
 * @link https://github.com/juliangut/janitor for the canonical source repository
 *
 * @license https://github.com/juliangut/janitor/blob/master/LICENSE
 */

namespace Janitor;

use Janitor\Handler\Render as RenderHandler;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

class Janitor
{
    /**
     * List of maintenance watchers.
     *
     * @var array
     */
    protected $watchers = [];

    /**
     * List of excluders conditions.
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
     * @param array                 $watchers
     * @param array                 $excluders
     * @param \Janitor\Handler|null $handler
     */
    public function __construct(array $watchers = [], array $excluders = [], callable $handler = null)
    {
        foreach ($watchers as $watcher) {
            $this->addWatcher($watcher);
        }

        foreach ($excluders as $excluder) {
            $this->addExcluder($excluder);
        }

        $this->handler = $handler;
    }

    /**
     * Add maintenance watcher.
     *
     * @param \Janitor\Watcher $watcher
     */
    public function addWatcher(Watcher $watcher)
    {
        $this->watchers[] = $watcher;

        return $this;
    }

    /**
     * Add excluder condition.
     *
     * @param \Janitor\Excluder $excluder
     */
    public function addExcluder(Excluder $excluder)
    {
        $this->excluders[] = $excluder;

        return $this;
    }

    /**
     * Set handler.
     *
     * @param callable $handler
     */
    public function setHandler(callable $handler)
    {
        $this->handler = $handler;

        return $this;
    }

    /**
     * Retrieve handler.
     *
     * @return callable
     */
    public function getHandler()
    {
        if (!is_callable($this->handler)) {
            $this->handler = new RenderHandler;
        }

        return $this->handler;
    }

    /**
     * Whether excluding conditions are met.
     *
     * @return bool
     */
    public function isExcluded()
    {
        foreach ($this->excluders as $excluder) {
            if ($excluder->isExcluded()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get currenlty active watcher.
     *
     * @return \Janitor\Watcher|null
     */
    public function getActiveWatcher()
    {
        foreach ($this->watchers as $watcher) {
            if ($watcher->isActive()) {
                return $watcher;
            }
        }

        return null;
    }

    /**
     * Whether maintenance mode is active.
     *
     * @return bool
     */
    public function inMaintenance()
    {
        return $this->getActiveWatcher() instanceof Watcher;
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
            if ($watcher instanceof ScheduledWatcher && $watcher->isScheduled()) {
                $scheduledTimes = array_merge($scheduledTimes, $watcher->getScheduledTimes($count));
            }
        }

        usort(
            $scheduledTimes,
            function ($time1, $time2) {
                if ($time1['start'] == $time2['start']) {
                    return 0;
                }

                return $time1['start'] < $time2['start'] ? -1 : 1;
            }
        );

        return array_slice($scheduledTimes, 0, $count);
    }

    /**
     * Handle maintenance mode.
     *
     * @param callable $handler
     *
     * @return mixed|null
     */
    public function handle(callable $handler)
    {
        if (!$this->isExcluded()) {
            $activeWatcher = $this->getActiveWatcher();

            if ($activeWatcher !== null) {
                return $handler($watcher);
            }
        }

        return null;
    }

    /**
     * Run middleware.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface      $response
     * @param Janitor\Watcher                          $watcher
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next)
    {
        if (!$this->isExcluded() && $activeWatcher = $this->getActiveWatcher()) {
            return call_user_func_array($this->getHandler(), [$request, $response, $activeWatcher]);
        }

        return $next($request, $response);
    }
}
