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
use Behat\Behat\Tester\Result\HookedStepContainerTestResult;
use Behat\Behat\Tester\Result\StepContainerTestResult;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\StepContainerInterface;
use Behat\Testwork\Call\CallResults;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Hook\HookDispatcher;
use Behat\Testwork\Suite\Suite;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Dispatching step container tester.
 *
 * Step container tester dispatching BEFORE/AFTER hooks and events during tests.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class HookableStepContainerTester extends StepContainerTester
{
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
     * @var string
     */
    private $hookEventClass;

    /**
     * Sets hook dispatcher and an event class it should use for hooks.
     *
     * @param HookDispatcher $hookDispatcher
     * @param string         $hookEventClass
     */
    public function setHookDispatcherAndEventClass(HookDispatcher $hookDispatcher, $hookEventClass)
    {
        $this->hookDispatcher = $hookDispatcher;
        $this->hookEventClass = $hookEventClass;
    }

    /**
     * Sets event dispatcher.
     *
     * @param EventDispatcherInterface $eventDispatcher
     * @param string                   $eventClass
     */
    public function setEventDispatcherAndEventClass(EventDispatcherInterface $eventDispatcher, $eventClass)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->eventClass = $eventClass;
    }

    /**
     * {@inheritdoc}
     */
    protected function testContainer(
        Suite $suite,
        Environment $environment,
        FeatureNode $feature,
        StepContainerInterface $container,
        $skip = false
    ) {
        $beforeHooks = (!$skip && $this->hookDispatcher)
            ? $this->dispatchBeforeHooks($suite, $environment, $feature, $container)
            :  new CallResults();
        $this->eventDispatcher && $this->dispatchBeforeEvent($suite, $environment, $feature, $container, $beforeHooks);

        $skip = $skip || $beforeHooks->hasExceptions();
        $result = parent::testContainer($suite, $environment, $feature, $container, $skip);

        $afterHooks = (!$skip && $this->hookDispatcher)
            ? $this->dispatchAfterHooks($suite, $environment, $feature, $container, $result)
            : new CallResults();
        $result = new HookedStepContainerTestResult(
            $result->getStepTestResults(),
            CallResults::merge($beforeHooks, $afterHooks)
        );
        $this->eventDispatcher && $this->dispatchAfterEvent($suite, $environment, $feature, $container, $result, $afterHooks);

        return $result;
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
        $class = $this->hookEventClass;
        $event = $event = new $class($suite, $environment, $feature, $container);

        return $this->hookDispatcher->dispatchEventHooks(ScenarioTested::BEFORE, $event);
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
        $class = $this->hookEventClass;
        $event = new $class($suite, $environment, $feature, $container, $result);

        return $this->hookDispatcher->dispatchEventHooks(ScenarioTested::AFTER, $event);
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
}
