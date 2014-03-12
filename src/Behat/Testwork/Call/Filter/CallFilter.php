<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Call\Filter;

use Behat\Testwork\Call\Call;
use Behat\Testwork\Call\CallCenter;

/**
 * Filters call before its being made and returns a new call.
 *
 * @see CallCenter
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface CallFilter
{
    /**
     * Checks if filter supports a call.
     *
     * @param Call $call
     *
     * @return Boolean
     */
    public function supportsCall(Call $call);

    /**
     * Filters a call and returns a new one.
     *
     * @param Call $call
     *
     * @return Call
     */
    public function filterCall(Call $call);
}
