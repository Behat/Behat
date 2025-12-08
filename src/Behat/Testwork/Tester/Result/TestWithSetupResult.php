<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Tester\Result;

use Behat\Testwork\Tester\Setup\Setup;
use Behat\Testwork\Tester\Setup\Teardown;

/**
 * Represents a test result with both setup and teardown attached.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class TestWithSetupResult implements TestResult
{
    /**
     * Initializes test result.
     */
    public function __construct(
        private readonly Setup $setup,
        private readonly TestResult $result,
        private readonly Teardown $teardown,
    ) {
    }

    public function isPassed(): bool
    {
        return self::PASSED == $this->getResultCode();
    }

    public function getResultCode()
    {
        if (!$this->setup->isSuccessful()) {
            return self::FAILED;
        }

        if (!$this->teardown->isSuccessful()) {
            return self::FAILED;
        }

        return $this->result->getResultCode();
    }
}
