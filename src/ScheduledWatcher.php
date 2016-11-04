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

/**
 * Scheduled maintenance watcher interface.
 */
interface ScheduledWatcher extends Watcher
{
    /**
     * Get scheduled time zone.
     *
     * @return \DateTimeZone
     */
    public function getTimeZone();

    /**
     * Set scheduled time zone.
     *
     * @param \DateTimeZone|string|int $timeZone
     *
     * @throws \InvalidArgumentException
     */
    public function setTimeZone($timeZone);

    /**
     * Get scheduled start time.
     *
     * @return \DateTime
     */
    public function getStart();

    /**
     * Get scheduled end time.
     *
     * @return \DateTime
     */
    public function getEnd();

    /**
     * Get schedule time span.
     *
     * @param int $count
     *
     * @return array
     */
    public function getScheduledTimes($count = 5);

    /**
     * Determines if maintenance mode is scheduled for a future date.
     *
     * @return bool
     */
    public function isScheduled();
}
