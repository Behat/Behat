<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Tester\Result;

use Behat\Testwork\Tester\Result\TestResult;

/**
 * Extends Testwork test result with support for undefined status.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface StepResult extends TestResult
{
    public const UNDEFINED = 30;
}
