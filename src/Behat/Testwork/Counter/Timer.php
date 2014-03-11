<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Counter;

use Behat\Testwork\Counter\Exception\TimerException;

/**
 * Provides time counting functionality.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Timer
{
    /**
     * @var null|float
     */
    private $starTime;
    /**
     * @var null|float
     */
    private $stopTime;

    /**
     * Starts timer.
     */
    public function start()
    {
        $this->starTime = microtime(true);
    }

    /**
     * Stops timer.
     *
     * @throws TimerException If timer has not been started
     */
    public function stop()
    {
        if (!$this->starTime) {
            throw new TimerException('You can not stop timer that has not been started.');
        }

        $this->stopTime = microtime(true);
    }

    /**
     * @return null|float
     *
     * @throws TimerException If timer has not been started
     */
    public function getTime()
    {
        if (!$this->starTime) {
            throw new TimerException('You can not get time from timer that never been started.');
        }

        $stopTime = $this->stopTime;
        if (!$this->stopTime) {
            $stopTime = microtime(true);
        }

        return $stopTime - $this->starTime;
    }

    /**
     * Returns number of minutes passed.
     *
     * @return integer
     */
    public function getMinutes()
    {
        return intval(floor($this->getTime() / 60));
    }

    /**
     * Returns number of seconds passed.
     *
     * @return float
     */
    public function getSeconds()
    {
        return round($this->getTime() - ($this->getMinutes() * 60), 3);
    }

    /**
     * Returns string representation of time passed.
     *
     * @return string
     */
    public function __toString()
    {
        if (!$this->starTime || !$this->stopTime) {
            return '0m0s';
        }

        return sprintf('%dm%.2fs', $this->getMinutes(), $this->getSeconds());
    }
}
