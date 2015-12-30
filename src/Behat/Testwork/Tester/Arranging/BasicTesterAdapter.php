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
use Behat\Testwork\Tester\Setup\SuccessfulSetup;
use Behat\Testwork\Tester\Setup\SuccessfulTeardown;
use Behat\Testwork\Tester\Tester;

/**
 * Adapts instances of basic Tester to the ArrangingTester interface.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class BasicTesterAdapter implements ArrangingTester
{
    /**
     * @var Tester
     */
    private $tester;

    /**
     * Initializes adapter.
     *
     * @param Tester $tester
     */
    public function __construct(Tester $tester)
    {
        $this->tester = $tester;
    }

    /**
     * {@inheritdoc}
     */
    public function setUp(TestContext $context, RunControl $ctrl)
    {
        return new SuccessfulSetup();
    }

    /**
     * {@inheritdoc}
     */
    public function test(TestContext $context, RunControl $control)
    {
        return $this->tester->test($context, $control);
    }

    /**
     * {@inheritdoc}
     */
    public function tearDown(TestContext $context, RunControl $control, TestResult $result)
    {
        return new SuccessfulTeardown();
    }
}
