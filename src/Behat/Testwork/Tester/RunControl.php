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
    /**
     * @var Boolean
     */
    private $skip = false;

    /**
     * Private constructor - use named constructors instead.
     */
    private function __construct()
    {
    }

    /**
     * Creates a new run control, that tells testers to run all tests.
     *
     * @return RunControl
     */
    public static function runAll()
    {
        return new RunControl();
    }

    /**
     * Creates a new run control, that enforces skipping of all tests and testing routines.
     *
     * @return RunControl
     */
    public static function skip()
    {
        $control = new RunControl();
        $control->skip = true;

        return $control;
    }

    /**
     * Checks if testing routines should be skipped.
     *
     * @return Boolean
     */
    public function isSkip()
    {
        return $this->skip;
    }
}
