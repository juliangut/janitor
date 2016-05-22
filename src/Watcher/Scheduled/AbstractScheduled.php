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
    public function setTimeZone($timeZone)
    {
        if (!$timeZone instanceof \DateTimeZone) {
            if (is_numeric($timeZone)) {
                $timeZoneName = timezone_name_from_abbr(null, $timeZone * 3600, true);
                if ($timeZoneName === false) {
                    throw new \InvalidArgumentException(sprintf('"%s" is not a valid time zone', $timeZone));
                }
                $timeZone = $timeZoneName;
            }

            try {
                $timeZone = timezone_open((string) $timeZone);
            // @codeCoverageIgnoreStart
            } catch (\Exception $exception) {
                throw new \InvalidArgumentException(sprintf('"%s" is not a valid time zone', $timeZone));
            }
            // @codeCoverageIgnoreEnd

            if ($timeZone === false) {
                throw new \InvalidArgumentException(sprintf('"%s" is not a valid time zone', $timeZone));
            }
        }

        $this->timeZone = $timeZone;
    }
}
