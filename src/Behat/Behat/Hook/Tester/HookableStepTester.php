<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Hook\Tester;

use Behat\Behat\Hook\Scope\AfterStepScope;
use Behat\Behat\Hook\Scope\BeforeStepScope;
use Behat\Behat\Tester\Result\StepResult;
use Behat\Behat\Tester\StepTester;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\StepNode;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Hook\HookDispatcher;
use Behat\Testwork\Hook\Tester\Setup\HookedSetup;
use Behat\Testwork\Hook\Tester\Setup\HookedTeardown;

/**
 * Step tester which dispatches hooks during its execution.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class HookableStepTester implements StepTester
{
    /**
     * @var StepTester
     */
    private $baseTester;
    /**
     * @var HookDispatcher
     */
    private $hookDispatcher;

    /**
     * Initializes tester.
     *
     * @param StepTester     $baseTester
     * @param HookDispatcher $hookDispatcher
     */
    public function __construct(StepTester $baseTester, HookDispatcher $hookDispatcher)
    {
        $this->baseTester = $baseTester;
        $this->hookDispatcher = $hookDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function setUp(Environment $env, FeatureNode $feature, StepNode $step, $skip)
    {
        $setup = $this->baseTester->setUp($env, $feature, $step, $skip);

        if ($skip) {
            return $setup;
        }

        $scope = new BeforeStepScope($env, $feature, $step);
        $hookCallResults = $this->hookDispatcher->dispatchScopeHooks($scope);

        return new HookedSetup($setup, $hookCallResults);
    }

    /**
     * {@inheritdoc}
     */
    public function test(Environment $env, FeatureNode $feature, StepNode $step, $skip)
    {
        return $this->baseTester->test($env, $feature, $step, $skip);
    }

    /**
     * {@inheritdoc}
     */
    public function tearDown(Environment $env, FeatureNode $feature, StepNode $step, $skip, StepResult $result)
    {
        $teardown = $this->baseTester->tearDown($env, $feature, $step, $skip, $result);

        if ($skip) {
            return $teardown;
        }

        $scope = new AfterStepScope($env, $feature, $step, $result);
        $hookCallResults = $this->hookDispatcher->dispatchScopeHooks($scope);

        return new HookedTeardown($teardown, $hookCallResults);
    }
}
