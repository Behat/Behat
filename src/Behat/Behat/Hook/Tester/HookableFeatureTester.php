<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Hook\Tester;

use Behat\Behat\Hook\Scope\AfterFeatureScope;
use Behat\Behat\Hook\Scope\BeforeFeatureScope;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Hook\HookDispatcher;
use Behat\Testwork\Hook\Tester\Setup\HookedSetup;
use Behat\Testwork\Hook\Tester\Setup\HookedTeardown;
use Behat\Testwork\Tester\Result\TestResult;
use Behat\Testwork\Tester\SpecificationTester;

/**
 * Feature tester which dispatches hooks during its execution.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class HookableFeatureTester implements SpecificationTester
{
    /**
     * @var SpecificationTester
     */
    private $baseTester;
    /**
     * @var HookDispatcher
     */
    private $hookDispatcher;

    /**
     * Initializes tester.
     *
     * @param SpecificationTester $baseTester
     * @param HookDispatcher      $hookDispatcher
     */
    public function __construct(SpecificationTester $baseTester, HookDispatcher $hookDispatcher)
    {
        $this->baseTester = $baseTester;
        $this->hookDispatcher = $hookDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function setUp(Environment $env, $spec, $skip)
    {
        $setup = $this->baseTester->setUp($env, $spec, $skip);

        if ($skip) {
            return $setup;
        }

        $scope = new BeforeFeatureScope($env, $spec);
        $hookCallResults = $this->hookDispatcher->dispatchScopeHooks($scope);

        return new HookedSetup($setup, $hookCallResults);
    }

    /**
     * {@inheritdoc}
     */
    public function test(Environment $env, $spec, $skip)
    {
        return $this->baseTester->test($env, $spec, $skip);
    }

    /**
     * {@inheritdoc}
     */
    public function tearDown(Environment $env, $spec, $skip, TestResult $result)
    {
        $teardown = $this->baseTester->tearDown($env, $spec, $skip, $result);

        if ($skip) {
            return $teardown;
        }

        $scope = new AfterFeatureScope($env, $spec, $result);
        $hookCallResults = $this->hookDispatcher->dispatchScopeHooks($scope);

        return new HookedTeardown($teardown, $hookCallResults);
    }
}
