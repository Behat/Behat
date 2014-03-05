<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\EventDispatcher\Tester;

use Behat\Behat\Tester\ExampleTester;
use Behat\Behat\Tester\ScenarioTester;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\StepContainerInterface;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Tester\Result\TestResult;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Behat event-dispatching scenario tester.
 *
 * Scenario tester dispatching BEFORE/AFTER events during tests.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class EventDispatchingScenarioTester implements ScenarioTester, ExampleTester
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
    private $eventClass;

    /**
     * Initializes tester.
     *
     * @param ScenarioTester           $baseTester
     * @param EventDispatcherInterface $eventDispatcher
     * @param string                   $eventClass
     */
    public function __construct(ScenarioTester $baseTester, EventDispatcherInterface $eventDispatcher, $eventClass)
    {
        $this->baseTester = $baseTester;
        $this->eventDispatcher = $eventDispatcher;
        $this->eventClass = $eventClass;
    }

    /**
     * {@inheritdoc}
     */
    public function setUp(Environment $environment, FeatureNode $feature, StepContainerInterface $scenario, $skip)
    {
        $eventClass = $this->eventClass;
        $event = new $eventClass($environment, $feature, $scenario);
        $this->eventDispatcher->dispatch($eventClass::BEFORE, $event);

        $this->baseTester->setUp($environment, $feature, $scenario, $skip);
    }

    /**
     * {@inheritdoc}
     */
    public function test(Environment $environment, FeatureNode $feature, StepContainerInterface $scenario, $skip)
    {
        return $this->baseTester->test($environment, $feature, $scenario, $skip);
    }

    /**
     * {@inheritdoc}
     */
    public function tearDown(
        Environment $environment,
        FeatureNode $feature,
        StepContainerInterface $scenario,
        $skip,
        TestResult $result
    ) {
        $eventClass = $this->eventClass;
        $event = new $eventClass($environment, $feature, $scenario, $result);
        $this->eventDispatcher->dispatch($eventClass::AFTER, $event);

        $this->baseTester->setUp($environment, $feature, $scenario, $skip, $result);
    }
}
