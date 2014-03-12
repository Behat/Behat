<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Call;

/**
 * Represents any call made inside testwork.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface Call
{
    /**
     * Returns callee.
     *
     * @return Callee
     */
    public function getCallee();

    /**
     * Returns bound callable.
     *
     * @return callable
     */
    public function getBoundCallable();

    /**
     * Returns call arguments.
     *
     * @return array
     */
    public function getArguments();

    /**
     * Returns call error reporting level.
     *
     * @return null|integer
     */
    public function getErrorReportingLevel();
}
