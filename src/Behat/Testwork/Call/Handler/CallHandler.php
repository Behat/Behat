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
use Behat\Testwork\Call\CallCenter;
use Behat\Testwork\Call\CallResult;

/**
 * Handles calls and produces call results.
 *
 * @see CallCenter
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
     * @return bool
     */
    public function supportsCall(Call $call);

    /**
     * Handles call and returns call result.
     *
     * @param Call $call
     *
     * @return CallResult
     */
    public function handleCall(Call $call);
}
