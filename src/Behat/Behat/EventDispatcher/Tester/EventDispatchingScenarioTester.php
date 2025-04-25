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
        $afterEventName,
    ) {
        $this->baseTester = $baseTester;
        $this->eventDispatcher = $eventDispatcher;
        $this->beforeEventName = $beforeEventName;
        $this->afterSetupEventName = $afterSetupEventName;
        $this->beforeTeardownEventName = $beforeTeardownEventName;
        $this->afterEventName = $afterEventName;
    }

    public function setUp(Environment $env, FeatureNode $feature, Scenario $scenario, $skip)
    {
        $event = new BeforeScenarioTested($env, $feature, $scenario);

        $this->eventDispatcher->dispatch($event, $this->beforeEventName);

        $setup = $this->baseTester->setUp($env, $feature, $scenario, $skip);

        $event = new AfterScenarioSetup($env, $feature, $scenario, $setup);

        $this->eventDispatcher->dispatch($event, $this->afterSetupEventName);

        return $setup;
    }

    public function test(Environment $env, FeatureNode $feature, Scenario $scenario, $skip)
    {
        return $this->baseTester->test($env, $feature, $scenario, $skip);
    }

    public function tearDown(Environment $env, FeatureNode $feature, Scenario $scenario, $skip, TestResult $result)
    {
        $event = new BeforeScenarioTeardown($env, $feature, $scenario, $result);

        $this->eventDispatcher->dispatch($event, $this->beforeTeardownEventName);

        $teardown = $this->baseTester->tearDown($env, $feature, $scenario, $skip, $result);

        $event = new AfterScenarioTested($env, $feature, $scenario, $result, $teardown);

        $this->eventDispatcher->dispatch($event, $this->afterEventName);

        return $teardown;
    }
}
