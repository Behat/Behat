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
use Behat\Behat\Definition\Call\RuntimeDefinition;
use Behat\Behat\Definition\DefinitionFinder;
use Behat\Behat\Definition\Exception\SearchException;
use Behat\Behat\Definition\SearchResult;
use Behat\Behat\Definition\Translator\TranslatedDefinition;
use Behat\Behat\Tester\Result\ExecutedStepResult;
use Behat\Behat\Tester\Result\FailedStepSearchResult;
use Behat\Behat\Tester\Result\SkippedStepResult;
use Behat\Behat\Tester\Result\StepResult;
use Behat\Behat\Tester\Result\UndefinedStepResult;
use Behat\Behat\Tester\StepTester;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\StepNode;
use Behat\Testwork\Call\CallCenter;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Tester\Setup\Setup;
use Behat\Testwork\Tester\Setup\SuccessfulSetup;
use Behat\Testwork\Tester\Setup\SuccessfulTeardown;
use Behat\Testwork\Tester\Setup\Teardown;

/**
 * Tester executing step tests in the runtime.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class RuntimeStepTester implements StepTester
{
    /**
     * Initialize tester.
     */
    public function __construct(
        private readonly DefinitionFinder $definitionFinder,
        private readonly CallCenter $callCenter,
    ) {
    }

    public function setUp(Environment $env, FeatureNode $feature, StepNode $step, $skip): Setup
    {
        return new SuccessfulSetup();
    }

    public function test(Environment $env, FeatureNode $feature, StepNode $step, $skip = false): StepResult
    {
        try {
            $search = $this->searchDefinition($env, $feature, $step);
            $result = $this->testDefinition($env, $feature, $step, $search, $skip);
        } catch (SearchException $exception) {
            $result = new FailedStepSearchResult($exception);
        }

        return $result;
    }

    public function tearDown(Environment $env, FeatureNode $feature, StepNode $step, $skip, StepResult $result): Teardown
    {
        return new SuccessfulTeardown();
    }

    /**
     * Searches for a definition.
     *
     * @return SearchResult
     */
    private function searchDefinition(Environment $env, FeatureNode $feature, StepNode $step)
    {
        return $this->definitionFinder->findDefinition($env, $feature, $step);
    }

    /**
     * Tests found definition.
     *
     * @param bool      $skip
     */
    private function testDefinition(Environment $env, FeatureNode $feature, StepNode $step, SearchResult $search, $skip): StepResult
    {
        if (!$search->hasMatch()) {
            return new UndefinedStepResult();
        }

        $definition = $search->getMatchedDefinition();
        // If the definition found is a translated definition, we need to mark the original definition
        if ($definition instanceof TranslatedDefinition) {
            $definition = $definition->getOriginalDefinition();
        }
        // If a definition has been found, we mark it as used even if it may be skipped,
        // as we want to count skipped definitions as used
        if ($definition instanceof RuntimeDefinition) {
            $definition->markAsUsed();
        }

        if ($skip) {
            return new SkippedStepResult($search);
        }

        $call = $this->createDefinitionCall($env, $feature, $search, $step);
        $result = $this->callCenter->makeCall($call);

        return new ExecutedStepResult($search, $result);
    }

    /**
     * Creates definition call.
     */
    private function createDefinitionCall(Environment $env, FeatureNode $feature, SearchResult $search, StepNode $step): DefinitionCall
    {
        $definition = $search->getMatchedDefinition();
        $arguments = $search->getMatchedArguments();

        return new DefinitionCall($env, $feature, $step, $definition, $arguments);
    }
}
