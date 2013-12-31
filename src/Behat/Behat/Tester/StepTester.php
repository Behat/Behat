<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Tester;

use Behat\Behat\Definition\Call\DefinitionCall;
use Behat\Behat\Definition\DefinitionFinder;
use Behat\Behat\Definition\Exception\SearchException;
use Behat\Behat\Definition\SearchResult;
use Behat\Behat\Tester\Event\StepTested;
use Behat\Behat\Tester\Result\StepTestResult;
use Behat\Behat\Tester\Result\TestResult;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\StepNode;
use Behat\Testwork\Call\CallCentre;
use Behat\Testwork\Call\CallResults;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Hook\HookDispatcher;
use Behat\Testwork\Suite\Suite;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Step tester.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class StepTester
{
    /**
     * @var DefinitionFinder
     */
    private $definitionFinder;
    /**
     * @var CallCentre
     */
    private $callCentre;
    /**
     * @var HookDispatcher
     */
    private $hookDispatcher;
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * Initialize tester.
     *
     * @param DefinitionFinder         $definitionFinder
     * @param CallCentre               $callCentre
     * @param HookDispatcher           $hookDispatcher
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        DefinitionFinder $definitionFinder,
        CallCentre $callCentre,
        HookDispatcher $hookDispatcher,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->definitionFinder = $definitionFinder;
        $this->callCentre = $callCentre;
        $this->hookDispatcher = $hookDispatcher;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Tests step.
     *
     * @param Suite       $suite
     * @param Environment $environment
     * @param FeatureNode $feature
     * @param StepNode    $step
     * @param Boolean     $skip
     *
     * @return TestResult
     */
    public function test(Suite $suite, Environment $environment, FeatureNode $feature, StepNode $step, $skip = false)
    {
        $beforeHooks = $skip ? new CallResults() : $this->dispatchBeforeHooks($suite, $environment, $feature, $step);
        $this->dispatchBeforeEvent($suite, $environment, $feature, $step, $beforeHooks);

        try {
            $search = $this->searchDefinition($environment, $feature, $step);
            $result = $this->testDefinition($environment, $feature, $step, $search, $beforeHooks, $skip);
        } catch (SearchException $exception) {
            $result = new StepTestResult(null, $exception, null, $beforeHooks);
        }

        $skip = TestResult::PASSED < $result->getResultCode() ? true : $skip;
        $afterHooks = $skip ? new CallResults() : $this->dispatchAfterHooks($suite, $environment, $feature, $step, $result);
        $this->dispatchAfterEvent($suite, $environment, $feature, $step, $result, $afterHooks);

        $result = new StepTestResult(
            $result->getSearchResult(),
            $result->getSearchException(),
            $result->getCallResult(),
            CallResults::merge($beforeHooks, $afterHooks)
        );

        return new TestResult($result->getResultCode());
    }

    /**
     * Dispatches BEFORE event.
     *
     * @param Suite       $suite
     * @param Environment $environment
     * @param FeatureNode $feature
     * @param StepNode    $step
     * @param CallResults $hookCallResults
     */
    private function dispatchBeforeEvent(
        Suite $suite,
        Environment $environment,
        FeatureNode $feature,
        StepNode $step,
        CallResults $hookCallResults
    ) {
        $event = new StepTested($suite, $environment, $feature, $step, null, $hookCallResults);

        $this->eventDispatcher->dispatch(StepTested::BEFORE, $event);
    }

    /**
     * Dispatches BEFORE event hooks.
     *
     * @param Suite       $suite
     * @param Environment $environment
     * @param FeatureNode $feature
     * @param StepNode    $step
     *
     * @return CallResults
     */
    private function dispatchBeforeHooks(Suite $suite, Environment $environment, FeatureNode $feature, StepNode $step)
    {
        $event = new StepTested($suite, $environment, $feature, $step);

        return $this->hookDispatcher->dispatchEventHooks(StepTested::BEFORE, $event);
    }

    /**
     * Searches for a definition.
     *
     * @param Environment $environment
     * @param FeatureNode $feature
     * @param StepNode    $step
     *
     * @return SearchResult
     */
    private function searchDefinition(Environment $environment, FeatureNode $feature, StepNode $step)
    {
        return $this->definitionFinder->findDefinition($environment, $feature, $step);
    }

    /**
     * Tests found definition.
     *
     * @param Environment  $environment
     * @param FeatureNode  $feature
     * @param StepNode     $step
     * @param SearchResult $search
     * @param CallResults  $hookResults
     * @param Boolean      $skip
     *
     * @return StepTestResult
     */
    private function testDefinition(
        Environment $environment,
        FeatureNode $feature,
        StepNode $step,
        SearchResult $search,
        CallResults $hookResults,
        $skip = false
    ) {
        if ($skip || !$search->hasMatch()) {
            return new StepTestResult($search, null, null, $hookResults);
        }

        $call = $this->createDefinitionCall($environment, $feature, $search, $step);
        $result = $this->callCentre->makeCall($call);

        return new StepTestResult($search, null, $result, $hookResults);
    }

    /**
     * Creates definition call.
     *
     * @param Environment  $environment
     * @param FeatureNode  $feature
     * @param SearchResult $search
     * @param StepNode     $step
     *
     * @return DefinitionCall
     */
    private function createDefinitionCall(
        Environment $environment,
        FeatureNode $feature,
        SearchResult $search,
        StepNode $step
    ) {
        $definition = $search->getMatchedDefinition();
        $arguments = $search->getMatchedArguments();

        return new DefinitionCall($environment, $feature, $step, $definition, $arguments);
    }

    /**
     * Dispatches AFTER event hooks.
     *
     * @param Suite          $suite
     * @param Environment    $environment
     * @param FeatureNode    $feature
     * @param StepNode       $step
     * @param StepTestResult $result
     *
     * @return CallResults
     */
    private function dispatchAfterHooks(
        Suite $suite,
        Environment $environment,
        FeatureNode $feature,
        StepNode $step,
        StepTestResult $result
    ) {
        $event = new StepTested($suite, $environment, $feature, $step, $result);

        return $this->hookDispatcher->dispatchEventHooks(StepTested::AFTER, $event);
    }

    /**
     * Dispatches AFTER event.
     *
     * @param Suite          $suite
     * @param Environment    $environment
     * @param FeatureNode    $feature
     * @param StepNode       $step
     * @param StepTestResult $result
     * @param CallResults    $hookCallResults
     */
    private function dispatchAfterEvent(
        Suite $suite,
        Environment $environment,
        FeatureNode $feature,
        StepNode $step,
        StepTestResult $result,
        CallResults $hookCallResults
    ) {
        $event = new StepTested($suite, $environment, $feature, $step, $result, $hookCallResults);

        $this->eventDispatcher->dispatch(StepTested::AFTER, $event);
    }
}
