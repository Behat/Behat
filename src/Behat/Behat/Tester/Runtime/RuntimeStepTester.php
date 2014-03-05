<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Tester\Runtime;

use Behat\Behat\Definition\Call\DefinitionCall;
use Behat\Behat\Definition\DefinitionFinder;
use Behat\Behat\Definition\Exception\SearchException;
use Behat\Behat\Definition\SearchResult;
use Behat\Behat\Tester\Exception\TearDownException;
use Behat\Behat\Tester\Result\StepTestResult;
use Behat\Behat\Tester\StepTester;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\StepNode;
use Behat\Testwork\Call\CallCenter;
use Behat\Testwork\Environment\Environment;

/**
 * Behat in-runtime step tester.
 *
 * Step tester executing step tests in the runtime.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class RuntimeStepTester implements StepTester
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
     * {@inheritdoc}
     */
    public function setUp(Environment $environment, FeatureNode $feature, StepNode $step, $skip)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function test(Environment $environment, FeatureNode $feature, StepNode $step, $skip = false)
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
     * {@inheritdoc}
     */
    public function tearDown(
        Environment $environment,
        FeatureNode $feature,
        StepNode $step,
        $skip,
        StepTestResult $result
    ) {
        if (StepTestResult::PASSED < $result->getResultCode()) {
            throw new TearDownException('Step test have failed.');
        }
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
