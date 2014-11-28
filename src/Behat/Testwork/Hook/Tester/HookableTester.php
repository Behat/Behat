<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Hook\Tester;

use Behat\Testwork\Hook\HookDispatcher;
use Behat\Testwork\Hook\Scope\ScopeFactory;
use Behat\Testwork\Hook\Tester\Setup\HookedSetup;
use Behat\Testwork\Hook\Tester\Setup\HookedTeardown;
use Behat\Testwork\Tester\Arranging\ArrangingTester;
use Behat\Testwork\Tester\Context\Context;
use Behat\Testwork\Tester\Result\TestResult;
use Behat\Testwork\Tester\RunControl;

/**
 * Adds hooking points to any ArrangingTester.
 *
 * With a help of BasicTesterAdapter can also add hooks to basic Tester instances.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class HookableTester implements ArrangingTester
{
    /**
     * @var ArrangingTester
     */
    private $decoratedTester;
    /**
     * @var ScopeFactory
     */
    private $scopeFactory;
    /**
     * @var HookDispatcher
     */
    private $hookDispatcher;

    /**
     * Initializes tester.
     *
     * @param ArrangingTester $decoratedTester
     * @param ScopeFactory    $scopeFactory
     * @param HookDispatcher  $hookDispatcher
     */
    public function __construct(
        ArrangingTester $decoratedTester,
        ScopeFactory $scopeFactory,
        HookDispatcher $hookDispatcher
    ) {
        $this->decoratedTester = $decoratedTester;
        $this->scopeFactory = $scopeFactory;
        $this->hookDispatcher = $hookDispatcher;
    }

    /**
     * Dispatches a `before` hook according to ScopeFactory.
     *
     * {@inheritdoc}
     */
    public function setUp(Context $context, RunControl $control)
    {
        $setup = $this->decoratedTester->setUp($context, $control);

        if ($control->isSkipEnforced()) {
            return $setup;
        }

        $scope = $this->scopeFactory->createBeforeHookScope($context);
        $hookCallResults = $this->hookDispatcher->dispatchScopeHooks($scope);

        return new HookedSetup($setup, $hookCallResults);
    }

    /**
     * Just proxies call to the decorated tester.
     *
     * {@inheritdoc}
     */
    public function test(Context $context, RunControl $control)
    {
        return $this->decoratedTester->test($context, $control);
    }

    /**
     * Dispatches an `after` hook according to ScopeFactory.
     *
     * {@inheritdoc}
     */
    public function tearDown(Context $context, RunControl $control, TestResult $result)
    {
        $teardown = $this->decoratedTester->tearDown($context, $control, $result);

        if ($control->isSkipEnforced()) {
            return $teardown;
        }

        $scope = $this->scopeFactory->createAfterHookScope($context, $result);
        $hookCallResults = $this->hookDispatcher->dispatchScopeHooks($scope);

        return new HookedTeardown($teardown, $hookCallResults);
    }
}
