<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\EventDispatcher\Tester;

use Behat\Testwork\EventDispatcher\Event\EventFactory;
use Behat\Testwork\Tester\Arranging\ArrangingTester;
use Behat\Testwork\Tester\Context\TestContext;
use Behat\Testwork\Tester\Control\RunControl;
use Behat\Testwork\Tester\Result\TestResult;
use Symfony\Component\EventDispatcher\EventDispatcherInterface as EventDispatcher;

/**
 * Makes any ArrangingTester to produce an events during the test lifecycle.
 *
 * With a help of BasicTesterAdapter can also add events support to basic Tester instances.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class EventDispatchingTester implements ArrangingTester
{
    /**
     * @var ArrangingTester
     */
    private $decoratedTester;
    /**
     * @var EventFactory
     */
    private $eventFactory;
    /**
     * @var EventDispatcher
     */
    private $eventDispatcher;

    /**
     * Initializes tester.
     *
     * @param ArrangingTester $decoratedTester
     * @param EventFactory    $eventFactory
     * @param EventDispatcher $eventDispatcher
     */
    public function __construct(
        ArrangingTester $decoratedTester,
        EventFactory $eventFactory,
        EventDispatcher $eventDispatcher
    ) {
        $this->decoratedTester = $decoratedTester;
        $this->eventFactory = $eventFactory;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Dispatches `beforeTested` event before setUp and `afterSetup` immediately after it.
     *
     * {@inheritdoc}
     */
    public function setUp(TestContext $context, RunControl $control)
    {
        $event = $this->eventFactory->createBeforeTestedEvent($context);
        $this->eventDispatcher->dispatch($event->getEventName(), $event);

        $setup = $this->decoratedTester->setUp($context, $control);

        $event = $this->eventFactory->createAfterSetupEvent($context, $setup);
        $this->eventDispatcher->dispatch($event->getEventName(), $event);

        return $setup;
    }

    /**
     * Just proxies call to the decorated tester.
     *
     * {@inheritdoc}
     */
    public function test(TestContext $context, RunControl $control)
    {
        return $this->decoratedTester->test($context, $control);
    }

    /**
     * Dispatches `beforeTeardown` event before tearDown and `afterTested` immediately after it.
     *
     * {@inheritdoc}
     */
    public function tearDown(TestContext $context, RunControl $control, TestResult $result)
    {
        $event = $this->eventFactory->createBeforeTeardownEvent($context, $result);
        $this->eventDispatcher->dispatch($event->getEventName(), $event);

        $teardown = $this->decoratedTester->tearDown($context, $control, $result);

        $event = $this->eventFactory->createAfterTestedEvent($context, $result, $teardown);
        $this->eventDispatcher->dispatch($event->getEventName(), $event);

        return $teardown;
    }
}
