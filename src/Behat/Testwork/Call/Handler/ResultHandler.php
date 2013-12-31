<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Call\Handler;

use Behat\Testwork\Call\CallCentre;
use Behat\Testwork\Call\CallResult;

/**
 * Testwork call result handler interface.
 *
 * Handles call results and produces new ones (if necessary).
 *
 * @see CallCentre
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface ResultHandler
{
    /**
     * Checks if handler supports call result.
     *
     * @param CallResult $result
     *
     * @return Boolean
     */
    public function supportsResult(CallResult $result);

    /**
     * Handles call result and returns either a new result or a null.
     *
     * @param CallResult $result
     *
     * @return null|CallResult
     */
    public function handleResult(CallResult $result);
}
