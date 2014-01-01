<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Tester;

use Behat\Behat\Tester\Event\BackgroundTested;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\StepNode;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Suite\Suite;
use Behat\Testwork\Tester\Result\TestResult;
use Behat\Testwork\Tester\Result\TestResults;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Background tester.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class BackgroundTester
{
    /**
     * @var StepTester
     */
    private $stepTester;
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * Initializes tester.
     *
     * @param StepTester               $stepTester
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(StepTester $stepTester, EventDispatcherInterface $eventDispatcher)
    {
        $this->stepTester = $stepTester;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Tests feature backgrounds.
     *
     * @param Suite       $suite
     * @param Environment $environment
     * @param FeatureNode $feature
     * @param Boolean     $skip
     *
     * @return TestResults
     */
    public function test(Suite $suite, Environment $environment, FeatureNode $feature, $skip = false)
    {
        $this->dispatchBeforeEvent($suite, $environment, $feature);
        $results = $this->testBackground($suite, $environment, $feature, $skip);
        $this->dispatchAfterEvent($suite, $environment, $feature, $results);

        return $results;
    }

    /**
     * Dispatches BEFORE event.
     *
     * @param Suite       $suite
     * @param Environment $environment
     * @param FeatureNode $feature
     */
    private function dispatchBeforeEvent(Suite $suite, Environment $environment, FeatureNode $feature)
    {
        $event = new BackgroundTested($suite, $environment, $feature, $feature->getBackground());

        $this->eventDispatcher->dispatch(BackgroundTested::BEFORE, $event);
    }

    /**
     * Tests background.
     *
     * @param Suite       $suite
     * @param Environment $environment
     * @param FeatureNode $feature
     * @param Boolean     $skip
     *
     * @return TestResults
     */
    private function testBackground(Suite $suite, Environment $environment, FeatureNode $feature, $skip = false)
    {
        $background = $feature->getBackground();

        $results = array();
        foreach ($background->getSteps() as $step) {
            $results[] = $lastResult = $this->testStep($suite, $environment, $feature, $step, $skip);
            $skip = TestResult::PASSED < $lastResult->getResultCode() ? true : $skip;
        }

        return new TestResults($results);
    }

    /**
     * Tests background step.
     *
     * @param Suite       $suite
     * @param Environment $environment
     * @param FeatureNode $feature
     * @param StepNode    $step
     * @param Boolean     $skip
     *
     * @return TestResult
     */
    private function testStep(
        Suite $suite,
        Environment $environment,
        FeatureNode $feature,
        StepNode $step,
        $skip = false
    ) {
        return $this->stepTester->test($suite, $environment, $feature, $step, $skip);
    }

    /**
     * Dispatches AFTER event.
     *
     * @param Suite       $suite
     * @param Environment $environment
     * @param FeatureNode $feature
     * @param TestResults $results
     */
    private function dispatchAfterEvent(
        Suite $suite,
        Environment $environment,
        FeatureNode $feature,
        TestResults $results
    ) {
        $event = new BackgroundTested($suite, $environment, $feature, $feature->getBackground(), $results);

        $this->eventDispatcher->dispatch(BackgroundTested::AFTER, $event);
    }
}
