<?php
/**
 * Effortless maintenance management (http://juliangut.com/janitor)
 *
 * @link https://github.com/juliangut/janitor for the canonical source repository
 *
 * @license https://github.com/juliangut/janitor/blob/master/LICENSE
 */

namespace Janitor\Watcher\Scheduled;

use Janitor\Exception\ScheduledException;
use DateTimeZone;
use Exception;

trait ScheduledTrait
{
    /**
     * Scheduled time zone.
     *
     * @var \DateTimeZone
     */
    protected $timeZone;

    /**
     * Set scheduled time zone.
     *
     * @param mixed $timeZone
     * @throws \Janitor\Exception\ScheduledException
     */
    public function setTimeZone($timeZone = null)
    {
        if ($timeZone !== null && !$timeZone instanceof DateTimeZone) {
            try {
                $timeZone = new DateTimeZone($timeZone);
            } catch (Exception $exception) {
                throw new ScheduledException($exception->getMessage());
            }
        }

        $this->timeZone = $timeZone;
    }

    /**
     * Get scheduled time zone.
     *
     * @return \DateTimeZone
     */
    public function getTimeZone()
    {
        if ($this->timeZone === null) {
            $this->timeZone = new DateTimeZone(date_default_timezone_get());
        }

        return $this->timeZone;
    }
}
