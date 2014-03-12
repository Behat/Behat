<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Hook\Tester;

use Behat\Behat\Hook\Scope\AfterScenarioScope;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Behat\Tester\ScenarioTester;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\ScenarioInterface as Scenario;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Hook\HookDispatcher;
use Behat\Testwork\Hook\Tester\Setup\HookedSetup;
use Behat\Testwork\Hook\Tester\Setup\HookedTeardown;
use Behat\Testwork\Tester\Result\TestResult;

/**
 * Scenario tester which dispatches hooks during its execution.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class HookableScenarioTester implements ScenarioTester
{
    /**
     * @var ScenarioTester
     */
    private $baseTester;
    /**
     * @var HookDispatcher
     */
    private $hookDispatcher;

    /**
     * Initializes tester.
     *
     * @param ScenarioTester $baseTester
     * @param HookDispatcher $hookDispatcher
     */
    public function __construct(ScenarioTester $baseTester, HookDispatcher $hookDispatcher)
    {
        $this->baseTester = $baseTester;
        $this->hookDispatcher = $hookDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function setUp(Environment $env, FeatureNode $feature, Scenario $scenario, $skip)
    {
        $setup = $this->baseTester->setUp($env, $feature, $scenario, $skip);

        if ($skip) {
            return $setup;
        }

        $scope = new BeforeScenarioScope($env, $feature, $scenario);
        $hookCallResults = $this->hookDispatcher->dispatchScopeHooks($scope);

        return new HookedSetup($setup, $hookCallResults);
    }

    /**
     * {@inheritdoc}
     */
    public function test(Environment $env, FeatureNode $feature, Scenario $scenario, $skip)
    {
        return $this->baseTester->test($env, $feature, $scenario, $skip);
    }

    /**
     * {@inheritdoc}
     */
    public function tearDown(Environment $env, FeatureNode $feature, Scenario $scenario, $skip, TestResult $result)
    {
        $teardown = $this->baseTester->tearDown($env, $feature, $scenario, $skip, $result);

        if ($skip) {
            return $teardown;
        }

        $scope = new AfterScenarioScope($env, $feature, $scenario, $result);
        $hookCallResults = $this->hookDispatcher->dispatchScopeHooks($scope);

        return new HookedTeardown($teardown, $hookCallResults);
    }
}
