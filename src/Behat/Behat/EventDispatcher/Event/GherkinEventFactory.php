<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\EventDispatcher\Event;

use Behat\Behat\Tester\Context\BackgroundContext;
use Behat\Behat\Tester\Context\ScenarioContext;
use Behat\Behat\Tester\Context\StepContext;
use Behat\Behat\Tester\Result\StepResult;
use Behat\Testwork\EventDispatcher\Event\EventFactory;
use Behat\Testwork\EventDispatcher\Event\ExerciseEventFactory;
use Behat\Testwork\Tester\Context\TestContext;
use Behat\Testwork\Tester\Context\SpecificationContext;
use Behat\Testwork\Tester\Result\TestResult as Result;
use Behat\Testwork\Tester\Setup\Setup;
use Behat\Testwork\Tester\Setup\Teardown;

/**
 * Gherkin-based event factory.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class GherkinEventFactory implements EventFactory
{
    /**
     * @var ExerciseEventFactory
     */
    private $exerciseFactory;

    /**
     * Initializes event factory.
     *
     * @param ExerciseEventFactory $factory
     */
    public function __construct(ExerciseEventFactory $factory)
    {
        $this->exerciseFactory = $factory;
    }

    /**
     * {@inheritdoc}
     */
    public function createBeforeTestedEvent(TestContext $context)
    {
        switch (true) {
            case $context instanceof StepContext:
                return new BeforeStepTested($context);

            case $context instanceof BackgroundContext:
                return new BeforeBackgroundTested($context);

            case ($context instanceof ScenarioContext && $context->isOutline()):
                return new BeforeOutlineTested($context);

            case ($context instanceof ScenarioContext && $context->isExample()):
                return new BeforeExampleTested($context);

            case $context instanceof ScenarioContext:
                return new BeforeScenarioTested($context);

            case $context instanceof SpecificationContext:
                return new BeforeFeatureTested($context);
        }

        return $this->exerciseFactory->createBeforeTestedEvent($context);
    }

    /**
     * {@inheritdoc}
     */
    public function createAfterSetupEvent(TestContext $context, Setup $setup)
    {
        switch (true) {
            case $context instanceof StepContext:
                return new AfterStepSetup($context, $setup);

            case $context instanceof BackgroundContext:
                return new AfterBackgroundSetup($context, $setup);

            case ($context instanceof ScenarioContext && $context->isOutline()):
                return new AfterOutlineSetup($context, $setup);

            case ($context instanceof ScenarioContext && $context->isExample()):
                return new AfterExampleSetup($context, $setup);

            case $context instanceof ScenarioContext:
                return new AfterScenarioSetup($context, $setup);

            case $context instanceof SpecificationContext:
                return new AfterFeatureSetup($context, $setup);
        }

        return $this->exerciseFactory->createAfterSetupEvent($context, $setup);
    }

    /**
     * {@inheritdoc}
     */
    public function createBeforeTeardownEvent(TestContext $context, Result $result)
    {
        switch (true) {
            case ($context instanceof StepContext && $result instanceof StepResult):
                return new BeforeStepTeardown($context, $result);

            case $context instanceof BackgroundContext:
                return new BeforeBackgroundTeardown($context, $result);

            case ($context instanceof ScenarioContext && $context->isOutline()):
                return new BeforeOutlineTeardown($context, $result);

            case ($context instanceof ScenarioContext && $context->isExample()):
                return new BeforeExampleTeardown($context, $result);

            case $context instanceof ScenarioContext:
                return new BeforeScenarioTeardown($context, $result);

            case $context instanceof SpecificationContext:
                return new BeforeFeatureTeardown($context, $result);
        }

        return $this->exerciseFactory->createBeforeTeardownEvent($context, $result);
    }

    /**
     * {@inheritdoc}
     */
    public function createAfterTestedEvent(TestContext $context, Result $result, Teardown $teardown)
    {
        switch (true) {
            case ($context instanceof StepContext && $result instanceof StepResult):
                return new AfterStepTested($context, $result, $teardown);

            case $context instanceof BackgroundContext:
                return new AfterBackgroundTested($context, $result, $teardown);

            case ($context instanceof ScenarioContext && $context->isOutline()):
                return new AfterOutlineTested($context, $result, $teardown);

            case ($context instanceof ScenarioContext && $context->isExample()):
                return new AfterExampleTested($context, $result, $teardown);

            case $context instanceof ScenarioContext:
                return new AfterScenarioTested($context, $result, $teardown);

            case $context instanceof SpecificationContext:
                return new AfterFeatureTested($context, $result, $teardown);
        }

        return $this->exerciseFactory->createAfterTestedEvent($context, $result, $teardown);
    }
}
