<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Hook\Tester;

use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Hook\HookDispatcher;
use Behat\Testwork\Hook\Scope\AfterSuiteScope;
use Behat\Testwork\Hook\Scope\BeforeSuiteScope;
use Behat\Testwork\Hook\Tester\Setup\HookedSetup;
use Behat\Testwork\Hook\Tester\Setup\HookedTeardown;
use Behat\Testwork\Specification\SpecificationIterator;
use Behat\Testwork\Tester\Result\TestResult;
use Behat\Testwork\Tester\SuiteTester;

/**
 * Suite tester which dispatches hooks during its execution.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * @template TSpec
 * @implements SuiteTester<TSpec>
 */
final class HookableSuiteTester implements SuiteTester
{
    /**
     * @var SuiteTester<TSpec>
     */
    private $baseTester;
    /**
     * @var HookDispatcher
     */
    private $hookDispatcher;

    /**
     * Initializes tester.
     *
     * @param SuiteTester<TSpec> $baseTester
     * @param HookDispatcher $hookDispatcher
     */
    public function __construct(SuiteTester $baseTester, HookDispatcher $hookDispatcher)
    {
        $this->baseTester = $baseTester;
        $this->hookDispatcher = $hookDispatcher;
    }

    public function setUp(Environment $env, SpecificationIterator $iterator, $skip)
    {
        $setup = $this->baseTester->setUp($env, $iterator, $skip);

        if ($skip) {
            return $setup;
        }

        $scope = new BeforeSuiteScope($env, $iterator);
        $hookCallResults = $this->hookDispatcher->dispatchScopeHooks($scope);

        return new HookedSetup($setup, $hookCallResults);
    }

    public function test(Environment $env, SpecificationIterator $iterator, $skip)
    {
        return $this->baseTester->test($env, $iterator, $skip);
    }

    public function tearDown(Environment $env, SpecificationIterator $iterator, $skip, TestResult $result)
    {
        $teardown = $this->baseTester->tearDown($env, $iterator, $skip, $result);

        if ($skip) {
            return $teardown;
        }

        $scope = new AfterSuiteScope($env, $iterator, $result);
        $hookCallResults = $this->hookDispatcher->dispatchScopeHooks($scope);

        return new HookedTeardown($teardown, $hookCallResults);
    }
}
