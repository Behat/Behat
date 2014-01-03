<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Tester;

use Behat\Behat\Tester\Event\ScenarioTested;
use Behat\Behat\Tester\Result\StepContainerTestResult;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\StepContainerInterface;
use Behat\Gherkin\Node\StepNode;
use Behat\Testwork\Call\CallResults;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Environment\EnvironmentManager;
use Behat\Testwork\Hook\HookDispatcher;
use Behat\Testwork\Suite\Suite;
use Behat\Testwork\Tester\Result\TestResult;
use Behat\Testwork\Tester\Result\TestResults;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Step container tester.
 *
 * Used to test both scenarios and outline examples.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class StepContainerTester
{
    /**
     * @var StepTester
     */
    private $stepTester;
    /**
     * @var BackgroundTester
     */
    private $backgroundTester;
    /**
     * @var EnvironmentManager
     */
    private $environmentManager;
    /**
     * @var HookDispatcher
     */
    private $hookDispatcher;
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;
    /**
     * @var string
     */
    private $eventClass;

    /**
     * Initializes tester.
     *
     * @param StepTester               $stepTester
     * @param BackgroundTester         $backgroundTester
     * @param EnvironmentManager       $environmentManager
     * @param HookDispatcher           $hookDispatcher
     * @param EventDispatcherInterface $eventDispatcher
     * @param string                   $eventClass
     */
    public function __construct(
        StepTester $stepTester,
        BackgroundTester $backgroundTester,
        EnvironmentManager $environmentManager,
        HookDispatcher $hookDispatcher,
        EventDispatcherInterface $eventDispatcher,
        $eventClass
    ) {
        $this->stepTester = $stepTester;
        $this->backgroundTester = $backgroundTester;
        $this->environmentManager = $environmentManager;
        $this->hookDispatcher = $hookDispatcher;
        $this->eventDispatcher = $eventDispatcher;
        $this->eventClass = $eventClass;
    }

    /**
     * Tests step container.
     *
     * @param Suite                  $suite
     * @param Environment            $environment
     * @param FeatureNode            $feature
     * @param StepContainerInterface $container
     * @param Boolean                $skip
     *
     * @return TestResult
     */
    public function test(
        Suite $suite,
        Environment $environment,
        FeatureNode $feature,
        StepContainerInterface $container,
        $skip = false
    ) {
        $environment = $this->isolateEnvironment($environment, $feature);

        $beforeHooks = $skip ? new CallResults() : $this->dispatchBeforeHooks($suite, $environment, $feature, $container);
        $this->dispatchBeforeEvent($suite, $environment, $feature, $container, $beforeHooks);

        $skip = $skip || $beforeHooks->hasExceptions();
        $result = $this->testContainer($suite, $environment, $feature, $container, $beforeHooks, $skip);

        $afterHooks = $skip ? new CallResults() : $this->dispatchAfterHooks($suite, $environment, $feature, $container, $result);
        $this->dispatchAfterEvent($suite, $environment, $feature, $container, $result, $afterHooks);

        $result = new StepContainerTestResult($result->getStepTestResults(), CallResults::merge($beforeHooks, $afterHooks));

        return new TestResult($result->getResultCode());
    }

    /**
     * Isolates test environment for a scenario run.
     *
     * @param Environment $environment
     * @param FeatureNode $feature
     *
     * @return Environment
     */
    private function isolateEnvironment(Environment $environment, FeatureNode $feature)
    {
        return $this->environmentManager->isolateEnvironment($environment, $feature);
    }

    /**
     * Dispatches BEFORE event.
     *
     * @param Suite                  $suite
     * @param Environment            $environment
     * @param FeatureNode            $feature
     * @param StepContainerInterface $container
     * @param CallResults            $hookCallResults
     */
    private function dispatchBeforeEvent(
        Suite $suite,
        Environment $environment,
        FeatureNode $feature,
        StepContainerInterface $container,
        CallResults $hookCallResults
    ) {
        $class = $this->eventClass;
        $event = new $class($suite, $environment, $feature, $container, null, $hookCallResults);

        $this->eventDispatcher->dispatch($class::BEFORE, $event);
    }

    /**
     * Dispatches BEFORE event hooks.
     *
     * @param Suite                  $suite
     * @param Environment            $environment
     * @param FeatureNode            $feature
     * @param StepContainerInterface $container
     *
     * @return CallResults
     */
    private function dispatchBeforeHooks(
        Suite $suite,
        Environment $environment,
        FeatureNode $feature,
        StepContainerInterface $container
    ) {
        $class = $this->eventClass;
        $event = $event = new $class($suite, $environment, $feature, $container);

        return $this->hookDispatcher->dispatchEventHooks(ScenarioTested::BEFORE, $event);
    }

    /**
     * Tests container node.
     *
     * @param Suite                  $suite
     * @param Environment            $environment
     * @param FeatureNode            $feature
     * @param StepContainerInterface $container
     * @param CallResults            $hookResults
     * @param Boolean                $skip
     *
     * @return StepContainerTestResult
     */
    private function testContainer(
        Suite $suite,
        Environment $environment,
        FeatureNode $feature,
        StepContainerInterface $container,
        CallResults $hookResults,
        $skip = false
    ) {
        $results = array();

        if ($feature->hasBackground()) {
            $backgroundResults = $this->testBackground($suite, $environment, $feature, $skip);
            $results = $backgroundResults->toArray();
            $skip = TestResult::PASSED < $backgroundResults->getResultCode() ? true : $skip;
        }

        foreach ($container->getSteps() as $step) {
            $results[] = $lastResult = $this->testStep($suite, $environment, $feature, $step, $skip);
            $skip = TestResult::PASSED < $lastResult->getResultCode() ? true : $skip;
        }

        return new StepContainerTestResult(new TestResults($results), $hookResults);
    }

    /**
     * Tests scenario background.
     *
     * @param Suite       $suite
     * @param Environment $environment
     * @param FeatureNode $feature
     * @param Boolean     $skip
     *
     * @return TestResults
     */
    private function testBackground(Suite $suite, Environment $environment, FeatureNode $feature, $skip = false)
    {
        return $this->backgroundTester->test($suite, $environment, $feature, $skip);
    }

    /**
     * Tests scenario step.
     *
     * @param Suite       $suite
     * @param Environment $environment
     * @param FeatureNode $feature
     * @param StepNode    $step
     * @param Boolean     $skip
     *
     * @return TestResult
     */
    private function testStep(
        Suite $suite,
        Environment $environment,
        FeatureNode $feature,
        StepNode $step,
        $skip = false
    ) {
        return $this->stepTester->test($suite, $environment, $feature, $step, $skip);
    }

    /**
     * Dispatches AFTER event hooks.
     *
     * @param Suite                   $suite
     * @param Environment             $environment
     * @param FeatureNode             $feature
     * @param StepContainerInterface  $container
     * @param StepContainerTestResult $result
     * @param CallResults             $hookCallResults
     */
    private function dispatchAfterEvent(
        Suite $suite,
        Environment $environment,
        FeatureNode $feature,
        StepContainerInterface $container,
        StepContainerTestResult $result,
        CallResults $hookCallResults
    ) {
        $class = $this->eventClass;
        $event = new $class($suite, $environment, $feature, $container, $result, $hookCallResults);

        $this->eventDispatcher->dispatch($class::AFTER, $event);
    }

    /**
     * Dispatches AFTER event.
     *
     * @param Suite                   $suite
     * @param Environment             $environment
     * @param FeatureNode             $feature
     * @param StepContainerInterface  $container
     * @param StepContainerTestResult $result
     *
     * @return CallResults
     */
    private function dispatchAfterHooks(
        Suite $suite,
        Environment $environment,
        FeatureNode $feature,
        StepContainerInterface $container,
        StepContainerTestResult $result
    ) {
        $class = $this->eventClass;
        $event = new $class($suite, $environment, $feature, $container, $result);

        return $this->hookDispatcher->dispatchEventHooks(ScenarioTested::AFTER, $event);
    }
}
