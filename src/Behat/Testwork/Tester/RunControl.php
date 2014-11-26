<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Tester;

/**
 * Represents a run control for tests.
 *
 * This object controls test execution flow.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class RunControl
{
    private $skip = false;

    /**
     * Forces tests to be skipped.
     *
     * @param Boolean $skip
     */
    public function enforceSkip($skip = true)
    {
        $this->skip = (bool)$skip;
    }

    /**
     * Checks if tests should be skipped.
     *
     * @return Boolean
     */
    public function isSkipEnforced()
    {
        return $this->skip;
    }
}
