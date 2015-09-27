<?php
/**
 * Effortless maintenance management (http://juliangut.com/janitor)
 *
 * @link https://github.com/juliangut/janitor for the canonical source repository
 * @license https://github.com/juliangut/janitor/blob/master/LICENSE
 */

namespace Janitor\Watcher\Scheduled;

use Janitor\ScheduledWatcher;

/**
 * Fixed date scheduled maintenance status provider.
 *
 * Maintenance mode is considered to be On if current date is
 *   - higher than start if only start is defined
 *   - lower than end if only end is defined
 *   - higher than start and lower than end if both are defined
 */
class Fixed implements ScheduledWatcher
{
    /**
     * Maintenance start time.
     *
     * @var \DateTime
     */
    protected $start;

    /**
     * Maintenance end time.
     *
     * @var \DateTime
     */
    protected $end;

    /**
     * @param mixed $start
     * @param mixed $end
     */
    public function __construct($start, $end)
    {
        $this->setStart($start);
        $this->setEnd($end);
    }

    /**
     * {@inheritDoc}
     */
    public function isActive()
    {
        $now = new \DateTime();

        if ($now < $this->start || $this->end < $now) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function getScheduledTimes($count = 5)
    {
        if (!$this->isScheduled()) {
            return [];
        }

        return [
            [
                'start' => $this->start,
                'end'   => $this->end,
            ]
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function isScheduled()
    {
        return $this->start && new \DateTime() < $this->start;
    }

    /**
     * Set scheduled start time.
     *
     * @param mixed $start
     * @throws \InvalidArgumentException
     */
    public function setStart($start)
    {
        if (!$start instanceof \DateTime) {
            try {
                $start = new \DateTime($start);
            } catch (\Exception $exception) {
                throw new \InvalidArgumentException(sprintf('"%s" is not a valid DateTime', $start));
            }
        }

        if ($this->end && $start > $this->end) {
            throw new \InvalidArgumentException('Start time should come before end time');
        }

        $this->start = $start;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * Set scheduled end time.
     *
     * @param mixed $end
     * @throws \InvalidArgumentException
     */
    public function setEnd($end)
    {
        if (!$end instanceof \DateTime) {
            try {
                $end = new \DateTime($end);
            } catch (\Exception $exception) {
                throw new \InvalidArgumentException(sprintf('"%s" is not a valid DateTime', $end));
            }
        }

        if ($this->start && $end < $this->start) {
            throw new \InvalidArgumentException('End time should come after start time');
        }

        $this->end = $end;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getEnd()
    {
        return $this->end;
    }
}
