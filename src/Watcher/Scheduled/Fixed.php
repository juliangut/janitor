<?php
/**
 * Effortless maintenance management (http://juliangut.com/janitor)
 *
 * @link https://github.com/juliangut/janitor for the canonical source repository
 *
 * @license https://github.com/juliangut/janitor/blob/master/LICENSE
 */

namespace Janitor\Watcher\Scheduled;

/**
 * Fixed date scheduled maintenance status watcher.
 *
 * Maintenance mode is considered to be On if current date is
 *   - higher than start if only start is defined
 *   - lower than end if only end is defined
 *   - higher than start and lower than end if both are defined
 */
class Fixed extends AbstractScheduled
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
     * @param mixed $timeZone
     */
    public function __construct($start, $end, $timeZone = null)
    {
        $this->setStart($start);
        $this->setEnd($end);
        $this->setTimeZone($timeZone);
    }

    /**
     * Set scheduled start time.
     *
     * @param mixed $start
     *
     * @throws \InvalidArgumentException
     */
    public function setStart($start)
    {
        if (!$start instanceof \DateTime) {
            try {
                $start = new \DateTime($start, $this->getTimeZone());
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
     * {@inheritdoc}
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * Set scheduled end time.
     *
     * @param mixed $end
     *
     * @throws \InvalidArgumentException
     */
    public function setEnd($end)
    {
        if (!$end instanceof \DateTime) {
            try {
                $end = new \DateTime($end, $this->getTimeZone());
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
     * {@inheritdoc}
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * {@inheritdoc}
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
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function isScheduled()
    {
        $now = new \DateTime('now', $this->getTimeZone());

        return $this->start && $now < $this->start;
    }

    /**
     * {@inheritdoc}
     */
    public function isActive()
    {
        $now = new \DateTime();

        return !($now < $this->start || $this->end < $now);
    }
}
