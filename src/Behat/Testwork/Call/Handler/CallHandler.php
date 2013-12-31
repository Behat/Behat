<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Call\Handler;

use Behat\Testwork\Call\Call;
use Behat\Testwork\Call\CallCentre;
use Behat\Testwork\Call\CallResult;

/**
 * Testwork call handler interface.
 *
 * Handles calls and produces call results.
 *
 * @see CallCentre
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface CallHandler
{
    /**
     * Checks if handler supports call.
     *
     * @param Call $call
     *
     * @return Boolean
     */
    public function supportsCall(Call $call);

    /**
     * Handles call and returns either new call, call result or a null.
     *
     * @param Call $call
     *
     * @return null|CallResult
     */
    public function handleCall(Call $call);
}
