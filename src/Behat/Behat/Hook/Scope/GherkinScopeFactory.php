<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Hook\Scope;

use Behat\Behat\Tester\Context\ScenarioContext;
use Behat\Behat\Tester\Context\StepContext;
use Behat\Behat\Tester\Result\StepResult;
use Behat\Testwork\Hook\Scope\ScopeFactory;
use Behat\Testwork\Hook\Scope\SuiteScopeFactory;
use Behat\Testwork\Tester\Context\TestContext;
use Behat\Testwork\Tester\Context\SpecificationContext;
use Behat\Testwork\Tester\Result\TestResult;

/**
 * Gherkin-based hook scope factory.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class GherkinScopeFactory implements ScopeFactory
{
    /**
     * @var SuiteScopeFactory
     */
    private $suiteFactory;

    /**
     * Initializes factory.
     *
     * @param SuiteScopeFactory $suiteFactory
     */
    public function __construct(SuiteScopeFactory $suiteFactory)
    {
        $this->suiteFactory = $suiteFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function createBeforeHookScope(TestContext $context)
    {
        switch (true) {
            case $context instanceof SpecificationContext:
                return new BeforeFeatureScope($context);

            case $context instanceof ScenarioContext:
                return new BeforeScenarioScope($context);

            case $context instanceof StepContext:
                return new BeforeStepScope($context);
        }

        return $this->suiteFactory->createBeforeHookScope($context);
    }

    /**
     * {@inheritdoc}
     */
    public function createAfterHookScope(TestContext $context, TestResult $result)
    {
        switch (true) {
            case $context instanceof SpecificationContext:
                return new AfterFeatureScope($context, $result);

            case $context instanceof ScenarioContext:
                return new AfterScenarioScope($context, $result);

            case ($context instanceof StepContext && $result instanceof StepResult):
                return new AfterStepScope($context, $result);
        }

        return $this->suiteFactory->createAfterHookScope($context, $result);
    }
}
