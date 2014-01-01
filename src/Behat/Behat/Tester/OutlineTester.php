<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Tester;

use Behat\Behat\Tester\Event\OutlineTested;
use Behat\Behat\Tester\Result\OutlineTestResult;
use Behat\Gherkin\Node\ExampleNode;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\OutlineNode;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Suite\Suite;
use Behat\Testwork\Tester\Result\TestResult;
use Behat\Testwork\Tester\Result\TestResults;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Outline tester.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class OutlineTester
{
    /**
     * @var StepContainerTester
     */
    private $exampleTester;
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * Initializes tester.
     *
     * @param StepContainerTester      $exampleTester
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(StepContainerTester $exampleTester, EventDispatcherInterface $eventDispatcher)
    {
        $this->exampleTester = $exampleTester;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Tests outline.
     *
     * @param Suite       $suite
     * @param Environment $environment
     * @param FeatureNode $feature
     * @param OutlineNode $outline
     * @param Boolean     $skip
     *
     * @return TestResult
     */
    public function test(
        Suite $suite,
        Environment $environment,
        FeatureNode $feature,
        OutlineNode $outline,
        $skip = false
    ) {
        $this->dispatchBeforeEvent($suite, $environment, $feature, $outline);
        $result = $this->testOutline($suite, $environment, $feature, $outline, $skip);
        $this->dispatchAfterEvent($suite, $environment, $feature, $outline, $result);

        return new TestResult($result->getResultCode());
    }

    /**
     * Dispatches BEFORE event.
     *
     * @param Suite       $suite
     * @param Environment $environment
     * @param FeatureNode $feature
     * @param OutlineNode $outline
     */
    private function dispatchBeforeEvent(
        Suite $suite,
        Environment $environment,
        FeatureNode $feature,
        OutlineNode $outline
    ) {
        $event = new OutlineTested($suite, $environment, $feature, $outline);

        $this->eventDispatcher->dispatch(OutlineTested::BEFORE, $event);
    }

    /**
     * Tests outline examples.
     *
     * @param Suite       $suite
     * @param Environment $environment
     * @param FeatureNode $feature
     * @param OutlineNode $outline
     * @param Boolean     $skip
     *
     * @return OutlineTestResult
     */
    private function testOutline(
        Suite $suite,
        Environment $environment,
        FeatureNode $feature,
        OutlineNode $outline,
        $skip = false
    ) {
        $results = array();
        foreach ($outline->getExamples() as $example) {
            $results[] = $this->testExample($suite, $environment, $feature, $example, $skip);
        }

        return new OutlineTestResult(new TestResults($results));
    }

    /**
     * Tests outline example.
     *
     * @param Suite       $suite
     * @param Environment $environment
     * @param FeatureNode $feature
     * @param ExampleNode $example
     * @param Boolean     $skip
     *
     * @return TestResult
     */
    private function testExample(
        Suite $suite,
        Environment $environment,
        FeatureNode $feature,
        ExampleNode $example,
        $skip = false
    ) {
        return $this->exampleTester->test($suite, $environment, $feature, $example, $skip);
    }

    /**
     * Dispatches AFTER event.
     *
     * @param Suite             $suite
     * @param Environment       $environment
     * @param FeatureNode       $feature
     * @param OutlineNode       $outline
     * @param OutlineTestResult $result
     */
    private function dispatchAfterEvent(
        Suite $suite,
        Environment $environment,
        FeatureNode $feature,
        OutlineNode $outline,
        OutlineTestResult $result
    ) {
        $event = new OutlineTested($suite, $environment, $feature, $outline, $result);

        $this->eventDispatcher->dispatch(OutlineTested::AFTER, $event);
    }
}
