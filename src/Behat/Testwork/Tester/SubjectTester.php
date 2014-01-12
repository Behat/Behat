<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Tester;

use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Tester\Result\TestResult;

/**
 * Testwork test subject tester.
 *
 * Implement this interface with custom tester.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface SubjectTester
{
    /**
     * Tests provided subject.
     *
     * @param Environment $environment
     * @param mixed       $testSubject
     * @param Boolean     $skip
     *
     * @return TestResult
     */
    public function test(Environment $environment, $testSubject, $skip = false);
}
