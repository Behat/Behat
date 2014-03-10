<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Call\Filter;

use Behat\Testwork\Call\CallCenter;
use Behat\Testwork\Call\CallResult;

/**
 * Filters call results and produces new ones.
 *
 * @see CallCenter
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface ResultFilter
{
    /**
     * Checks if filter supports call result.
     *
     * @param CallResult $result
     *
     * @return Boolean
     */
    public function supportsResult(CallResult $result);

    /**
     * Filters call result and returns a new result.
     *
     * @param CallResult $result
     *
     * @return CallResult
     */
    public function filterResult(CallResult $result);
}
