<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\EventDispatcher\Tester;

use Behat\Behat\EventDispatcher\Event\AfterScenarioSetup;
use Behat\Behat\EventDispatcher\Event\AfterScenarioTested;
use Behat\Behat\EventDispatcher\Event\BeforeScenarioTeardown;
use Behat\Behat\EventDispatcher\Event\BeforeScenarioTested;
use Behat\Behat\Tester\ScenarioTester;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\ScenarioInterface as Scenario;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\EventDispatcher\TestworkEventDispatcher;
use Behat\Testwork\Tester\Result\TestResult;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Scenario tester dispatching BEFORE/AFTER events during tests.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class EventDispatchingScenarioTester implements ScenarioTester
{
    /**
     * @var ScenarioTester
     */
    private $baseTester;
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;
    /**
     * @var string
     */
    private $beforeEventName;
    /**
     * @var string
     */
    private $afterSetupEventName;
    /**
     * @var string
     */
    private $beforeTeardownEventName;
    /**
     * @var string
     */
    private $afterEventName;

    /**
     * Initializes tester.
     *
     * @param ScenarioTester           $baseTester
     * @param EventDispatcherInterface $eventDispatcher
     * @param string                   $beforeEventName
     * @param string                   $afterSetupEventName
     * @param string                   $beforeTeardownEventName
     * @param string                   $afterEventName
     */
    public function __construct(
        ScenarioTester $baseTester,
        EventDispatcherInterface $eventDispatcher,
        $beforeEventName,
        $afterSetupEventName,
        $beforeTeardownEventName,
        $afterEventName
    ) {
        $this->baseTester = $baseTester;
        $this->eventDispatcher = $eventDispatcher;
        $this->beforeEventName = $beforeEventName;
        $this->afterSetupEventName = $afterSetupEventName;
        $this->beforeTeardownEventName = $beforeTeardownEventName;
        $this->afterEventName = $afterEventName;
    }

    /**
     * {@inheritdoc}
     */
    public function setUp(Environment $env, FeatureNode $feature, Scenario $scenario, $skip)
    {
        $event = new BeforeScenarioTested($env, $feature, $scenario);

        if (TestworkEventDispatcher::DISPATCHER_VERSION === 2) {
            $this->eventDispatcher->dispatch($event, $this->beforeEventName);
        } else {
            $this->eventDispatcher->dispatch($this->beforeEventName, $event);
        }

        $setup = $this->baseTester->setUp($env, $feature, $scenario, $skip);

        $event = new AfterScenarioSetup($env, $feature, $scenario, $setup);

        if (TestworkEventDispatcher::DISPATCHER_VERSION === 2) {
            $this->eventDispatcher->dispatch($event, $this->afterSetupEventName);
        } else {
            $this->eventDispatcher->dispatch($this->afterSetupEventName, $event);
        }

        return $setup;
    }

    /**
     * {@inheritdoc}
     */
    public function test(Environment $env, FeatureNode $feature, Scenario $scenario, $skip)
    {
        return $this->baseTester->test($env, $feature, $scenario, $skip);
    }

    /**
     * {@inheritdoc}
     */
    public function tearDown(Environment $env, FeatureNode $feature, Scenario $scenario, $skip, TestResult $result)
    {
        $event = new BeforeScenarioTeardown($env, $feature, $scenario, $result);

        if (TestworkEventDispatcher::DISPATCHER_VERSION === 2) {
            $this->eventDispatcher->dispatch($event, $this->beforeTeardownEventName);
        } else {
            $this->eventDispatcher->dispatch($this->beforeTeardownEventName, $event);
        }

        $teardown = $this->baseTester->tearDown($env, $feature, $scenario, $skip, $result);

        $event = new AfterScenarioTested($env, $feature, $scenario, $result, $teardown);

        if (TestworkEventDispatcher::DISPATCHER_VERSION === 2) {
            $this->eventDispatcher->dispatch($event, $this->afterEventName);
        } else {
            $this->eventDispatcher->dispatch($this->afterEventName, $event);
        }

        return $teardown;
    }
}
