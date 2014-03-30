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
     * @var Setup
     */
    private $setup;
    /**
     * @var TestResult
     */
    private $result;
    /**
     * @var Teardown
     */
    private $teardown;

    /**
     * Initializes test result.
     *
     * @param Setup      $setup
     * @param TestResult $result
     * @param Teardown   $teardown
     */
    public function __construct(Setup $setup, TestResult $result, Teardown $teardown)
    {
        $this->setup = $setup;
        $this->result = $result;
        $this->teardown = $teardown;
    }

    /**
     * {@inheritdoc}
     */
    public function isPassed()
    {
        return self::PASSED == $this->getResultCode();
    }

    /**
     * {@inheritdoc}
     */
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
