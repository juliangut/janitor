<?php
/**
 * Effortless maintenance management (http://juliangut.com/janitor)
 *
 * @link https://github.com/juliangut/janitor for the canonical source repository
 *
 * @license https://github.com/juliangut/janitor/blob/master/LICENSE
 */

namespace Janitor\Watcher\Scheduled;

use Janitor\ScheduledWatcher;

/**
 * Class AbstractScheduled
 */
abstract class AbstractScheduled implements ScheduledWatcher
{
    /**
     * Scheduled time zone.
     *
     * @var \DateTimeZone
     */
    protected $timeZone;

    /**
     * {@inheritdoc}
     */
    public function getTimeZone()
    {
        if ($this->timeZone === null) {
            $this->timeZone = new \DateTimeZone(date_default_timezone_get());
        }

        return $this->timeZone;
    }

    /**
     * {@inheritdoc}
     */
    public function setTimeZone($timeZone = null)
    {
        if ($timeZone !== null && !$timeZone instanceof \DateTimeZone) {
            try {
                $timeZone = new \DateTimeZone($timeZone);
            } catch (\Exception $exception) {
                throw new \InvalidArgumentException(sprintf('"%s" is not a valid time zone', $timeZone));
            }
        }

        $this->timeZone = $timeZone;
    }
}
