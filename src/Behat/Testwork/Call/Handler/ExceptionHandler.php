<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Call\Handler;

use Throwable;

/**
 * Handles exceptions.
 *
 * @see CallCenter
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface ExceptionHandler
{
    /**
     * Checks if handler supports exception.
     */
    public function supportsException(Throwable $exception): bool;

    /**
     * Handles exception and returns new one if necessary.
     */
    public function handleException(Throwable $exception): Throwable;
}
