<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Tester\Arranging;

use Behat\Testwork\Tester\Context\TestContext;
use Behat\Testwork\Tester\Control\RunControl;
use Behat\Testwork\Tester\Result\TestResult;
use Behat\Testwork\Tester\Setup\Setup;
use Behat\Testwork\Tester\Setup\Teardown;
use Behat\Testwork\Tester\Tester;

/**
 * Represents a tester that requires arrange actions (setUp, tearDown).
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface ArrangingTester extends Tester
{
    /**
     * Sets up test for a provided context according to the run control.
     *
     * @param TestContext $context
     * @param RunControl  $control
     *
     * @return Setup
     */
    public function setUp(TestContext $context, RunControl $control);

    /**
     * Tears down test for a provided context according to the run control.
     *
     * @param TestContext $context
     * @param RunControl  $control
     * @param TestResult  $result
     *
     * @return Teardown
     */
    public function tearDown(TestContext $context, RunControl $control, TestResult $result);
}
