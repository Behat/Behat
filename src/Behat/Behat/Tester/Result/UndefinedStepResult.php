<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Tester\Result;

/**
 * Behat undefined step result.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class UndefinedStepResult implements StepResult
{
    /**
     * Checks that test has passed.
     *
     * @return Boolean
     */
    public function isPassed()
    {
        return false;
    }

    /**
     * Returns tester result code.
     *
     * @return integer
     */
    public function getResultCode()
    {
        return self::UNDEFINED;
    }
}
