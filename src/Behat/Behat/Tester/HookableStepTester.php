<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Tester;

use Behat\Behat\Tester\Event\StepTested;
use Behat\Behat\Tester\Result\HookedStepTestResult;
use Behat\Behat\Tester\Result\StepTestResult;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\StepNode;
use Behat\Testwork\Call\CallResults;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Hook\HookDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Hookable step tester.
 *
 * Step tester dispatching BEFORE/AFTER hooks and events during tests.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class HookableStepTester extends StepTester
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;
    /**
     * @var HookDispatcher
     */
    private $hookDispatcher;

    /**
     * Sets hook dispatcher.
     *
     * @param HookDispatcher $hookDispatcher
     */
    public function setHookDispatcher(HookDispatcher $hookDispatcher)
    {
        $this->hookDispatcher = $hookDispatcher;
    }

    /**
     * Sets event dispatcher.
     *
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    protected function testStep(Environment $environment, FeatureNode $feature, StepNode $step, $skip)
    {
        $beforeHooks = (!$skip && $this->hookDispatcher)
            ? $this->dispatchBeforeHooks($environment, $feature, $step)
            : new CallResults();
        $this->eventDispatcher and $this->dispatchBeforeEvent($environment, $feature, $step, $beforeHooks);

        $skip = $skip || $beforeHooks->hasExceptions();
        $result = parent::testStep($environment, $feature, $step, $skip);

        $afterHooks = (!$skip && $this->hookDispatcher)
            ? $this->dispatchAfterHooks($environment, $feature, $step, $result)
            : new CallResults();
        $result = new HookedStepTestResult(
            $result->getSearchResult(),
            $result->getSearchException(),
            $result->getCallResult(),
            CallResults::merge($beforeHooks, $afterHooks)
        );
        $this->eventDispatcher and $this->dispatchAfterEvent($environment, $feature, $step, $result, $afterHooks);

        return $result;
    }

    /**
     * Dispatches BEFORE event hooks.
     *
     * @param Environment $environment
     * @param FeatureNode $feature
     * @param StepNode    $step
     *
     * @return CallResults
     */
    private function dispatchBeforeHooks(Environment $environment, FeatureNode $feature, StepNode $step)
    {
        $event = new StepTested($environment, $feature, $step);

        return $this->hookDispatcher->dispatchEventHooks(StepTested::BEFORE, $event);
    }

    /**
     * Dispatches BEFORE event.
     *
     * @param Environment $environment
     * @param FeatureNode $feature
     * @param StepNode    $step
     * @param CallResults $hookCallResults
     */
    private function dispatchBeforeEvent(
        Environment $environment,
        FeatureNode $feature,
        StepNode $step,
        CallResults $hookCallResults
    ) {
        $event = new StepTested($environment, $feature, $step, null, $hookCallResults);

        $this->eventDispatcher->dispatch(StepTested::BEFORE, $event);
    }

    /**
     * Dispatches AFTER event hooks.
     *
     * @param Environment    $environment
     * @param FeatureNode    $feature
     * @param StepNode       $step
     * @param StepTestResult $result
     *
     * @return CallResults
     */
    private function dispatchAfterHooks(
        Environment $environment,
        FeatureNode $feature,
        StepNode $step,
        StepTestResult $result
    ) {
        $event = new StepTested($environment, $feature, $step, $result);

        return $this->hookDispatcher->dispatchEventHooks(StepTested::AFTER, $event);
    }

    /**
     * Dispatches AFTER event.
     *
     * @param Environment    $environment
     * @param FeatureNode    $feature
     * @param StepNode       $step
     * @param StepTestResult $result
     * @param CallResults    $hookCallResults
     */
    private function dispatchAfterEvent(
        Environment $environment,
        FeatureNode $feature,
        StepNode $step,
        StepTestResult $result,
        CallResults $hookCallResults
    ) {
        $event = new StepTested($environment, $feature, $step, $result, $hookCallResults);

        $this->eventDispatcher->dispatch(StepTested::AFTER, $event);
    }
}
