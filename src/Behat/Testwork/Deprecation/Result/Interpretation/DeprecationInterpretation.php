<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Deprecation\Result\Interpretation;

use Behat\Testwork\Deprecation\DeprecationCollector;
use Behat\Testwork\Tester\Result\Interpretation\ResultInterpretation;
use Behat\Testwork\Tester\Result\TestResult;

/**
 * Interprets test results as failures if any Behat deprecations were triggered.
 */
final class DeprecationInterpretation implements ResultInterpretation
{
    public function __construct(
        private readonly DeprecationCollector $collector,
    ) {
    }

    public function isFailure(TestResult $result): bool
    {
        return $this->collector->hasDeprecations();
    }
}
