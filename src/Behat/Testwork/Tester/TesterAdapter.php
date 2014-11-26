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
use Behat\Testwork\Tester\Setup\SuccessfulSetup;
use Behat\Testwork\Tester\Setup\SuccessfulTeardown;

/**
 * Adapts instances of Tester to ArrangingTester.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class TesterAdapter implements ArrangingTester
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
    public function setUp(Context $context, RunControl $ctrl)
    {
        return new SuccessfulSetup();
    }

    /**
     * {@inheritdoc}
     */
    public function test(Context $context, RunControl $control)
    {
        return $this->tester->test($context, $control);
    }

    /**
     * {@inheritdoc}
     */
    public function tearDown(Context $context, RunControl $control, TestResult $result)
    {
        return new SuccessfulTeardown();
    }
}
