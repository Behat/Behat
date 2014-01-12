<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Tester;

use Behat\Behat\Definition\Call\DefinitionCall;
use Behat\Behat\Definition\DefinitionFinder;
use Behat\Behat\Definition\Exception\SearchException;
use Behat\Behat\Definition\SearchResult;
use Behat\Behat\Tester\Result\StepTestResult;
use Behat\Behat\Tester\Result\TestResult;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\StepNode;
use Behat\Testwork\Call\CallCenter;
use Behat\Testwork\Environment\Environment;

/**
 * Step tester.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class StepTester
{
    /**
     * @var DefinitionFinder
     */
    private $definitionFinder;
    /**
     * @var CallCenter
     */
    private $callCenter;

    /**
     * Initialize tester.
     *
     * @param DefinitionFinder $definitionFinder
     * @param CallCenter       $callCenter
     */
    public function __construct(DefinitionFinder $definitionFinder, CallCenter $callCenter)
    {
        $this->definitionFinder = $definitionFinder;
        $this->callCenter = $callCenter;
    }

    /**
     * Tests step.
     *
     * @param Environment $environment
     * @param FeatureNode $feature
     * @param StepNode    $step
     * @param Boolean     $skip
     *
     * @return TestResult
     */
    public function test(Environment $environment, FeatureNode $feature, StepNode $step, $skip = false)
    {
        $result = $this->testStep($environment, $feature, $step, $skip);

        return new TestResult($result->getResultCode());
    }

    /**
     * @param Environment $environment
     * @param FeatureNode $feature
     * @param StepNode    $step
     * @param             $skip
     *
     * @return StepTestResult
     */
    protected function testStep(Environment $environment, FeatureNode $feature, StepNode $step, $skip)
    {
        try {
            $search = $this->searchDefinition($environment, $feature, $step);
            $result = $this->testDefinition($environment, $feature, $step, $search, $skip);
        } catch (SearchException $exception) {
            $result = new StepTestResult(null, $exception, null);
        }

        return $result;
    }

    /**
     * Searches for a definition.
     *
     * @param Environment $environment
     * @param FeatureNode $feature
     * @param StepNode    $step
     *
     * @return SearchResult
     */
    private function searchDefinition(Environment $environment, FeatureNode $feature, StepNode $step)
    {
        return $this->definitionFinder->findDefinition($environment, $feature, $step);
    }

    /**
     * Tests found definition.
     *
     * @param Environment  $environment
     * @param FeatureNode  $feature
     * @param StepNode     $step
     * @param SearchResult $search
     * @param Boolean      $skip
     *
     * @return StepTestResult
     */
    private function testDefinition(
        Environment $environment,
        FeatureNode $feature,
        StepNode $step,
        SearchResult $search,
        $skip = false
    ) {
        if ($skip || !$search->hasMatch()) {
            return new StepTestResult($search, null, null);
        }

        $call = $this->createDefinitionCall($environment, $feature, $search, $step);
        $result = $this->callCenter->makeCall($call);

        return new StepTestResult($search, null, $result);
    }

    /**
     * Creates definition call.
     *
     * @param Environment  $environment
     * @param FeatureNode  $feature
     * @param SearchResult $search
     * @param StepNode     $step
     *
     * @return DefinitionCall
     */
    private function createDefinitionCall(
        Environment $environment,
        FeatureNode $feature,
        SearchResult $search,
        StepNode $step
    ) {
        $definition = $search->getMatchedDefinition();
        $arguments = $search->getMatchedArguments();

        return new DefinitionCall($environment, $feature, $step, $definition, $arguments);
    }
}
