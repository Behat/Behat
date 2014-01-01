<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Tester;

use Behat\Behat\Tester\Event\FeatureTested;
use Behat\Behat\Tester\Result\FeatureTestResult;
use Behat\Behat\Tester\Result\OutlineTestResult;
use Behat\Behat\Tester\Result\StepContainerTestResult;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\OutlineNode;
use Behat\Gherkin\Node\ScenarioInterface;
use Behat\Gherkin\Node\StepContainerInterface;
use Behat\Testwork\Call\CallResults;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Hook\HookDispatcher;
use Behat\Testwork\Suite\Suite;
use Behat\Testwork\Tester\Result\TestResult;
use Behat\Testwork\Tester\Result\TestResults;
use Behat\Testwork\Tester\SubjectTester;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Feature tester.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class FeatureTester implements SubjectTester
{
    /**
     * @var StepContainerTester
     */
    private $scenarioTester;
    /**
     * @var OutlineTester
     */
    private $outlineTester;
    /**
     * @var HookDispatcher
     */
    private $hookDispatcher;
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * Initializes tester.
     *
     * @param StepContainerTester      $scenarioTester
     * @param OutlineTester            $outlineTester
     * @param HookDispatcher           $hookDispatcher
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        StepContainerTester $scenarioTester,
        OutlineTester $outlineTester,
        HookDispatcher $hookDispatcher,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->scenarioTester = $scenarioTester;
        $this->outlineTester = $outlineTester;
        $this->hookDispatcher = $hookDispatcher;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Tests feature.
     *
     * @param Suite       $suite
     * @param Environment $environment
     * @param FeatureNode $feature
     * @param Boolean     $skip
     *
     * @return TestResult
     */
    public function test(Suite $suite, Environment $environment, $feature, $skip = false)
    {
        $beforeHooks = $skip ? new CallResults() : $this->dispatchBeforeHooks($suite, $environment, $feature);
        $this->dispatchBeforeEvent($suite, $environment, $feature, $beforeHooks);

        $skip = $beforeHooks->hasExceptions() ? true : $skip;
        $result = $this->testFeature($suite, $environment, $feature, $beforeHooks, $skip);

        $afterHooks = $skip ? new CallResults() : $this->dispatchAfterHooks($suite, $environment, $feature, $result);
        $this->dispatchAfterEvent($suite, $environment, $feature, $result, $afterHooks);

        $result = new FeatureTestResult($result->getScenarioTestResults(), CallResults::merge($beforeHooks, $afterHooks));

        return new TestResult($result->getResultCode());
    }

    /**
     * Dispatches BEFORE event.
     *
     * @param Suite       $suite
     * @param Environment $environment
     * @param FeatureNode $feature
     * @param CallResults $hookCallResults
     */
    private function dispatchBeforeEvent(
        Suite $suite,
        Environment $environment,
        FeatureNode $feature,
        CallResults $hookCallResults
    ) {
        $event = new FeatureTested($suite, $environment, $feature, null, $hookCallResults);

        $this->eventDispatcher->dispatch(FeatureTested::BEFORE, $event);
    }

    /**
     * Dispatches BEFORE event hooks.
     *
     * @param Suite       $suite
     * @param Environment $environment
     * @param FeatureNode $feature
     *
     * @return CallResults
     */
    private function dispatchBeforeHooks(Suite $suite, Environment $environment, FeatureNode $feature)
    {
        $event = new FeatureTested($suite, $environment, $feature);

        return $this->hookDispatcher->dispatchEventHooks(FeatureTested::BEFORE, $event);
    }

    /**
     * Tests feature scenarios.
     *
     * @param Suite       $suite
     * @param Environment $environment
     * @param FeatureNode $feature
     * @param CallResults $hookResults
     * @param Boolean     $skip
     *
     * @return FeatureTestResult
     */
    private function testFeature(
        Suite $suite,
        Environment $environment,
        FeatureNode $feature,
        CallResults $hookResults,
        $skip = false
    ) {
        $results = array();
        foreach ($feature->getScenarios() as $scenario) {
            $results[] = $this->testScenario($suite, $environment, $feature, $scenario, $skip);
        }

        return new FeatureTestResult(new TestResults($results), $hookResults);
    }

    /**
     * Tests any scenario.
     *
     * @param Suite             $suite
     * @param Environment       $environment
     * @param FeatureNode       $feature
     * @param ScenarioInterface $scenario
     * @param Boolean           $skip
     *
     * @return TestResult
     */
    private function testScenario(
        Suite $suite,
        Environment $environment,
        FeatureNode $feature,
        ScenarioInterface $scenario,
        $skip = false
    ) {
        if ($scenario instanceof OutlineNode) {
            return $this->testOutlineNode($suite, $environment, $feature, $scenario, $skip);
        }

        return $this->testScenarioNode($suite, $environment, $feature, $scenario, $skip);
    }

    /**
     * Tests scenario outline node.
     *
     * @param Suite       $suite
     * @param Environment $environment
     * @param FeatureNode $feature
     * @param OutlineNode $scenario
     * @param Boolean     $skip
     *
     * @return OutlineTestResult
     */
    private function testOutlineNode(
        Suite $suite,
        Environment $environment,
        FeatureNode $feature,
        OutlineNode $scenario,
        $skip = false
    ) {
        return $this->outlineTester->test($suite, $environment, $feature, $scenario, $skip);
    }

    /**
     * Tests scenario node.
     *
     * @param Suite                  $suite
     * @param Environment            $environment
     * @param FeatureNode            $feature
     * @param StepContainerInterface $scenario
     * @param Boolean                $skip
     *
     * @return StepContainerTestResult
     */
    private function testScenarioNode(
        Suite $suite,
        Environment $environment,
        FeatureNode $feature,
        StepContainerInterface $scenario,
        $skip = false
    ) {
        return $this->scenarioTester->test($suite, $environment, $feature, $scenario, $skip);
    }

    /**
     * Dispatches AFTER event hooks.
     *
     * @param Suite             $suite
     * @param Environment       $environment
     * @param FeatureNode       $feature
     * @param FeatureTestResult $result
     *
     * @return CallResults
     */
    private function dispatchAfterHooks(
        Suite $suite,
        Environment $environment,
        FeatureNode $feature,
        FeatureTestResult $result
    ) {
        $event = new FeatureTested($suite, $environment, $feature, $result);

        return $this->hookDispatcher->dispatchEventHooks(FeatureTested::AFTER, $event);
    }

    /**
     * Dispatches AFTER event.
     *
     * @param Suite             $suite
     * @param Environment       $environment
     * @param FeatureNode       $feature
     * @param FeatureTestResult $result
     * @param CallResults       $hookCallResults
     */
    private function dispatchAfterEvent(
        Suite $suite,
        Environment $environment,
        FeatureNode $feature,
        FeatureTestResult $result,
        CallResults $hookCallResults
    ) {
        $event = new FeatureTested($suite, $environment, $feature, $result, $hookCallResults);

        $this->eventDispatcher->dispatch(FeatureTested::AFTER, $event);
    }
}
