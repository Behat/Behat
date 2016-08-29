<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Call\Handler;

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
     *
     * @param \Throwable $exception
     *
     * @return bool
     */
    public function supportsException($exception);

    /**
     * Handles exception and returns new one if necessary.
     *
     * @param \Throwable $exception
     *
     * @return \Throwable
     */
    public function handleException($exception);
}
