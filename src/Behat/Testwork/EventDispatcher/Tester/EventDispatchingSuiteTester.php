<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\EventDispatcher\Tester;

use Behat\Testwork\Environment\Environment;
use Behat\Testwork\EventDispatcher\Event\AfterSuiteSetup;
use Behat\Testwork\EventDispatcher\Event\AfterSuiteTested;
use Behat\Testwork\EventDispatcher\Event\BeforeSuiteTeardown;
use Behat\Testwork\EventDispatcher\Event\BeforeSuiteTested;
use Behat\Testwork\EventDispatcher\TestworkEventDispatcher;
use Behat\Testwork\Specification\SpecificationIterator;
use Behat\Testwork\Tester\Result\TestResult;
use Behat\Testwork\Tester\SuiteTester;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Suite tester dispatching BEFORE/AFTER events during testing.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class EventDispatchingSuiteTester implements SuiteTester
{
    /**
     * @var SuiteTester
     */
    private $baseTester;
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * Initializes tester.
     *
     * @param SuiteTester              $baseTester
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(SuiteTester $baseTester, EventDispatcherInterface $eventDispatcher)
    {
        $this->baseTester = $baseTester;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function setUp(Environment $env, SpecificationIterator $iterator, $skip)
    {
        $event = new BeforeSuiteTested($env, $iterator);

        if (TestworkEventDispatcher::DISPATCHER_VERSION === 2) {
            $this->eventDispatcher->dispatch($event, $event::BEFORE);
        } else {
            $this->eventDispatcher->dispatch($event::BEFORE, $event);
        }

        $setup = $this->baseTester->setUp($env, $iterator, $skip);

        $event = new AfterSuiteSetup($env, $iterator, $setup);

        if (TestworkEventDispatcher::DISPATCHER_VERSION === 2) {
            $this->eventDispatcher->dispatch($event, $event::AFTER_SETUP);
        } else {
            $this->eventDispatcher->dispatch($event::AFTER_SETUP, $event);
        }

        return $setup;
    }

    /**
     * {@inheritdoc}
     */
    public function test(Environment $env, SpecificationIterator $iterator, $skip = false)
    {
        return $this->baseTester->test($env, $iterator, $skip);
    }

    /**
     * {@inheritdoc}
     */
    public function tearDown(Environment $env, SpecificationIterator $iterator, $skip, TestResult $result)
    {
        $event = new BeforeSuiteTeardown($env, $iterator, $result);
        if (TestworkEventDispatcher::DISPATCHER_VERSION === 2) {
            $this->eventDispatcher->dispatch( $event, $event::BEFORE_TEARDOWN);
        } else {
            $this->eventDispatcher->dispatch($event::BEFORE_TEARDOWN, $event);
        }

        $teardown = $this->baseTester->tearDown($env, $iterator, $skip, $result);

        $event = new AfterSuiteTested($env, $iterator, $result, $teardown);

        if (TestworkEventDispatcher::DISPATCHER_VERSION === 2) {
            $this->eventDispatcher->dispatch( $event, $event::AFTER);
        } else {
            $this->eventDispatcher->dispatch($event::AFTER, $event);
        }

        return $teardown;
    }
}
