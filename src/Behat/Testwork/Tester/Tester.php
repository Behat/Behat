<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Tester;

use Behat\Testwork\Tester\Context\Context;
use Behat\Testwork\Tester\Result\TestResult;

/**
 * Tests provided context according to the run control.
 *
 * All Testwork testers must implement this interface.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface Tester
{
    /**
     * Tests provided context according to the run control.
     *
     * @param Context    $context
     * @param RunControl $control
     *
     * @return TestResult
     */
    public function test(Context $context, RunControl $control);
}
