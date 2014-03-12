<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Tester\Result\Interpretation;

use Behat\Testwork\Tester\Result\TestResult;

/**
 * Interprets test results strictly - everything that is not an explicit pass is a failure.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class StrictInterpretation implements ResultInterpretation
{
    /**
     * {@inheritdoc}
     */
    public function isFailure(TestResult $result)
    {
        return !$result->isPassed();
    }
}
