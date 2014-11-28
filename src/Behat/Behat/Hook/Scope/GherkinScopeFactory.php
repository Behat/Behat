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
use Behat\Testwork\Hook\Exception\UnsupportedContextException;
use Behat\Testwork\Hook\Scope\AfterSuiteScope;
use Behat\Testwork\Hook\Scope\BeforeSuiteScope;
use Behat\Testwork\Hook\Scope\ScopeFactory;
use Behat\Testwork\Tester\Context\Context;
use Behat\Testwork\Tester\Context\SpecificationContext;
use Behat\Testwork\Tester\Context\SuiteContext;
use Behat\Testwork\Tester\Result\TestResult;

/**
 * Gherkin-based hook scope factory.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class GherkinScopeFactory implements ScopeFactory
{
    /**
     * {@inheritdoc}
     */
    public function createBeforeHookScope(Context $context)
    {
        switch (true) {
            case $context instanceof SuiteContext:
                return new BeforeSuiteScope($context);

            case $context instanceof SpecificationContext:
                return new BeforeFeatureScope($context);

            case $context instanceof ScenarioContext:
                return new BeforeScenarioScope($context);

            case $context instanceof StepContext:
                return new BeforeStepScope($context);
        }

        throw new UnsupportedContextException(
            sprintf(
                'Can not create a hook scope for context `%s`.',
                get_class($context)
            ), $context
        );
    }

    /**
     * {@inheritdoc}
     */
    public function createAfterHookScope(Context $context, TestResult $result)
    {
        switch (true) {
            case $context instanceof SuiteContext:
                return new AfterSuiteScope($context, $result);

            case $context instanceof SpecificationContext:
                return new AfterFeatureScope($context, $result);

            case $context instanceof ScenarioContext:
                return new AfterScenarioScope($context, $result);

            case ($context instanceof StepContext && $result instanceof StepResult):
                return new AfterStepScope($context, $result);
        }

        throw new UnsupportedContextException(
            sprintf(
                'Can not create a hook scope for context class `%s` and result class `%s`.',
                get_class($context),
                get_class($result)
            ), $context
        );
    }
}
