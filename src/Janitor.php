<?php
/**
 * Effortless maintenance management (http://juliangut.com/janitor)
 *
 * @link https://github.com/juliangut/janitor for the canonical source repository
 * @license https://github.com/juliangut/janitor/blob/master/LICENSE
 */

namespace Janitor;

use Janitor\Strategy\Render as RenderStrategy;

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
     * Resolve strategy.
     *
     * @var Janitor\Strategy
     */
    protected $strategy;

    /**
     * @param array $watchers
     * @param array $excluders
     * @param Janitor\Strategy $strategy
     */
    public function __construct(array $watchers = [], array $excluders = [], Strategy $strategy = null)
    {
        foreach ($watchers as $watcher) {
            $this->addWatcher($watcher);
        }

        foreach ($excluders as $excluder) {
            $this->addExcluder($excluder);
        }

        $this->strategy = $strategy;
    }

    /**
     * Handle maintenance mode.
     *
     * @return bool
     */
    public function handle()
    {
        if (!$this->isExcluded()) {
            foreach ($this->watchers as $watcher) {
                if ($watcher->isActive()) {
                    if (!$this->strategy instanceof Strategy) {
                        $this->strategy = new RenderStrategy;
                    }

                    call_user_func([$this->strategy, 'handle'], $watcher);

                    return true;
                }
            }
        }

        return false;
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
     * Get currenlty active watcher.
     *
     * @return \Janitor\Watcher
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
     * Get next scheduled time spans.
     *
     * Returns an array of ['start' => \DateTime, 'end' => \DateTime]
     *
     * @param  integer $count
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
     * Set strategy.
     *
     * @param \Janitor\Strategy $strategy
     */
    public function setStrategy(Strategy $strategy)
    {
        $this->strategy = $strategy;

        return $this;
    }
}
