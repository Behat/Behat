<?php

/*
 * This file is part of the behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Tester\Control;

use Behat\Testwork\Tester\Context\TestContext;

/**
 * Very basic run-control that can either force all tests to run or force them all to skip.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class BasicRunControl implements RunControl
{
    /**
     * @var Boolean
     */
    private $runAll = true;

    /**
     * Private constructor - use named constructors instead.
     */
    private function __construct()
    {
    }

    /**
     * Creates a new run control, that tells all testers to run all tests.
     *
     * @return BasicRunControl
     */
    public static function runAll()
    {
        return new BasicRunControl();
    }

    /**
     * Creates a new run control, that enforces skipping of all tests and testing routines.
     *
     * @return BasicRunControl
     */
    public static function skipAll()
    {
        $control = new BasicRunControl();
        $control->runAll = false;

        return $control;
    }

    /**
     * {@inheritdoc}
     */
    public function isContextTestable(TestContext $context)
    {
        return $this->runAll;
    }
}
