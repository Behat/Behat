<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Tester;

use Behat\Testwork\Call\CallResults;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Hook\HookDispatcher;
use Behat\Testwork\Subject\SubjectIterator;
use Behat\Testwork\Tester\Event\SuiteTested;
use Behat\Testwork\Tester\Result\HookedSuiteTestResult;
use Behat\Testwork\Tester\Result\SuiteTestResult;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Testwork dispatching suite tester.
 *
 * Suite tester dispatching BEFORE/AFTER hooks and events during tests.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class HookableSuiteTester extends SuiteTester
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
    protected function testSuite(Environment $environment, SubjectIterator $iterator, $skip = false)
    {
        $beforeHooks = (!$skip && $this->hookDispatcher)
            ? $this->dispatchBeforeHooks($environment)
            : new CallResults();
        $this->eventDispatcher and $this->dispatchBeforeEvent($environment, $beforeHooks);

        $skip = $skip || $beforeHooks->hasExceptions();
        $result = parent::testSuite($environment, $iterator, $skip);

        $afterHooks = (!$skip && $this->hookDispatcher)
            ? $this->dispatchAfterHooks($environment, $result)
            : new CallResults();
        $result = new HookedSuiteTestResult(
            $result->getSubjectTestResults(),
            CallResults::merge($beforeHooks, $afterHooks)
        );
        $this->eventDispatcher and $this->dispatchAfterEvent($environment, $result, $afterHooks);

        return $result;
    }

    /**
     * Dispatches BEFORE event hooks.
     *
     * @param Environment $environment
     *
     * @return CallResults
     */
    private function dispatchBeforeHooks(Environment $environment)
    {
        return $this->hookDispatcher->dispatchEventHooks(SuiteTested::BEFORE, new SuiteTested($environment));
    }

    /**
     * Dispatches BEFORE event.
     *
     * @param Environment $environment
     * @param CallResults $hookCallResults
     */
    private function dispatchBeforeEvent(Environment $environment, CallResults $hookCallResults)
    {
        $this->eventDispatcher->dispatch(SuiteTested::BEFORE, new SuiteTested($environment, null, $hookCallResults));
    }

    /**
     * Dispatches AFTER event hooks.
     *
     * @param Environment     $environment
     * @param SuiteTestResult $result
     *
     * @return CallResults
     */
    private function dispatchAfterHooks(Environment $environment, SuiteTestResult $result)
    {
        return $this->hookDispatcher->dispatchEventHooks(SuiteTested::AFTER, new SuiteTested($environment, $result));
    }

    /**
     * Dispatches AFTER event.
     *
     * @param Environment     $environment
     * @param SuiteTestResult $result
     * @param CallResults     $hookCallResults
     */
    private function dispatchAfterEvent(Environment $environment, SuiteTestResult $result, CallResults $hookCallResults)
    {
        $this->eventDispatcher->dispatch(SuiteTested::AFTER, new SuiteTested($environment, $result, $hookCallResults));
    }
}
