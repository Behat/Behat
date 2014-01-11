<?php

/*
 * This file is part of the Behat.
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
use Behat\Testwork\Suite\Suite;
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
class DispatchingSuiteTester extends SuiteTester
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
    public function setHookDispatcher($hookDispatcher)
    {
        $this->hookDispatcher = $hookDispatcher;
    }

    /**
     * Sets event dispatcher.
     *
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function setEventDispatcher($eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    protected function testSuite(Environment $environment, Suite $suite, SubjectIterator $iterator, $skip = false)
    {
        $beforeHooks = (!$skip && $this->hookDispatcher)
            ? $this->dispatchBeforeHooks($suite, $environment)
            : new CallResults();
        $this->eventDispatcher && $this->dispatchBeforeEvent($suite, $environment, $beforeHooks);

        $skip = $skip || $beforeHooks->hasExceptions();
        $result = parent::testSuite($environment, $suite, $iterator, $skip);

        $afterHooks = (!$skip && $this->hookDispatcher)
            ? $this->dispatchAfterHooks($suite, $environment, $result)
            : new CallResults();
        $this->eventDispatcher && $this->dispatchAfterEvent($suite, $environment, $result, $afterHooks);

        return new HookedSuiteTestResult(
            $result->getSubjectTestResults(),
            CallResults::merge($beforeHooks, $afterHooks)
        );
    }

    /**
     * Dispatches BEFORE event hooks.
     *
     * @param Suite       $suite
     * @param Environment $environment
     *
     * @return CallResults
     */
    private function dispatchBeforeHooks(Suite $suite, Environment $environment)
    {
        $event = new SuiteTested($suite, $environment);

        return $this->hookDispatcher->dispatchEventHooks(SuiteTested::BEFORE, $event);
    }

    /**
     * Dispatches BEFORE event.
     *
     * @param Suite       $suite
     * @param Environment $environment
     * @param CallResults $hookCallResults
     */
    private function dispatchBeforeEvent(Suite $suite, Environment $environment, CallResults $hookCallResults)
    {
        $event = new SuiteTested($suite, $environment, null, $hookCallResults);

        $this->eventDispatcher->dispatch(SuiteTested::BEFORE, $event);
    }

    /**
     * Dispatches AFTER event hooks.
     *
     * @param Suite           $suite
     * @param Environment     $environment
     * @param SuiteTestResult $result
     *
     * @return CallResults
     */
    private function dispatchAfterHooks(Suite $suite, Environment $environment, SuiteTestResult $result)
    {
        $event = new SuiteTested($suite, $environment, $result);

        return $this->hookDispatcher->dispatchEventHooks(SuiteTested::AFTER, $event);
    }

    /**
     * Dispatches AFTER event.
     *
     * @param Suite           $suite
     * @param Environment     $environment
     * @param SuiteTestResult $result
     * @param CallResults     $hookCallResults
     */
    private function dispatchAfterEvent(
        Suite $suite,
        Environment $environment,
        SuiteTestResult $result,
        CallResults $hookCallResults
    ) {
        $this->eventDispatcher->dispatch(
            SuiteTested::AFTER,
            new SuiteTested($suite, $environment, $result, $hookCallResults)
        );
    }
}
