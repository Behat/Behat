<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Tester\Arranging;

use Behat\Testwork\Tester\Context\Context;
use Behat\Testwork\Tester\Result\IntegerTestResult;
use Behat\Testwork\Tester\Result\TestWithSetupResult;
use Behat\Testwork\Tester\RunControl;
use Behat\Testwork\Tester\Tester;

/**
 * Adapts instances of ArrangingTester to the basic Tester interface.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class ArrangingTesterAdapter implements Tester
{
    /**
     * @var ArrangingTester
     */
    private $arrangingTester;

    /**
     * Initializes adapter.
     *
     * @param ArrangingTester $arrangingTester
     */
    public function __construct(ArrangingTester $arrangingTester)
    {
        $this->arrangingTester = $arrangingTester;
    }

    /**
     * {@inheritdoc}
     */
    public function test(Context $context, RunControl $control)
    {
        $localControl = clone $control;

        $setup = $this->arrangingTester->setUp($context, $localControl);
        $localControl->enforceSkip(!$setup->isSuccessful() || $control->isSkipEnforced());
        $testResult = $this->arrangingTester->test($context, $localControl);
        $teardown = $this->arrangingTester->tearDown($context, $localControl, $testResult);

        $integerResult = new IntegerTestResult($testResult->getResultCode());

        return new TestWithSetupResult($setup, $integerResult, $teardown);
    }
}
