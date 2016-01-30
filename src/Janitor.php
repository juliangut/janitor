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
     * @param array         $watchers
     * @param array         $excluders
     * @param callable|null $handler
     * @param string        $attributeName
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
     * Set request attribute name to store active watcher.
     *
     * @param string $attributeName
     */
    public function setAttributeName($attributeName)
    {
        $this->attributeName = $attributeName;

        return $this;
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
     * Run middleware.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface      $response
     * @param callable                                 $next
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next)
    {
        $activeWatcher = $this->getActiveWatcher();

        if ($activeWatcher instanceof Watcher) {
            if (!$this->isExcluded($request)) {
                return call_user_func_array($this->getHandler(), [$request, $response, $activeWatcher]);
            }

            $request = $request->withAttribute($this->attributeName, $activeWatcher);
        }

        return $next($request, $response);
    }

    /**
     * Get currenlty active watcher.
     *
     * @return \Janitor\Watcher|null
     */
    protected function getActiveWatcher()
    {
        foreach ($this->watchers as $watcher) {
            if ($watcher->isActive()) {
                return $watcher;
            }
        }

        return null;
    }

    /**
     * Whether excluding conditions are met.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
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
