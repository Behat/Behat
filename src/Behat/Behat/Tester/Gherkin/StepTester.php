<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Tester\Gherkin;

use Behat\Behat\Definition\Call\DefinitionCall;
use Behat\Behat\Definition\DefinitionFinder;
use Behat\Behat\Definition\Exception\SearchException;
use Behat\Behat\Definition\SearchResult;
use Behat\Behat\Tester\Context\StepContext;
use Behat\Behat\Tester\Result\ExecutedStepResult;
use Behat\Behat\Tester\Result\FailedStepSearchResult;
use Behat\Behat\Tester\Result\SkippedStepResult;
use Behat\Behat\Tester\Result\StepResult;
use Behat\Behat\Tester\Result\UndefinedStepResult;
use Behat\Testwork\Call\CallCenter;
use Behat\Testwork\Tester\Context\TestContext;
use Behat\Testwork\Tester\Control\RunControl;
use Behat\Testwork\Tester\Exception\WrongContextException;
use Behat\Testwork\Tester\Tester;

/**
 * Tests provided Gherkin step.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class StepTester implements Tester
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
    public function test(TestContext $context, RunControl $control)
    {
        $context = $this->castContext($context);

        try {
            $search = $this->searchDefinition($context);
            $result = $this->testDefinition($context, $control, $search);
        } catch (SearchException $exception) {
            $result = new FailedStepSearchResult($exception);
        }

        return $result;
    }

    /**
     * Searches for a definition.
     *
     * @param StepContext $context
     *
     * @return SearchResult
     */
    private function searchDefinition(StepContext $context)
    {
        return $this->definitionFinder->findDefinition(
            $context->getEnvironment(),
            $context->getFeature(),
            $context->getStep()
        );
    }

    /**
     * Tests found definition.
     *
     * @param StepContext  $context
     * @param RunControl   $control
     * @param SearchResult $search
     *
     * @return StepResult
     */
    private function testDefinition(StepContext $context, RunControl $control, SearchResult $search)
    {
        if (!$search->hasMatch()) {
            return new UndefinedStepResult();
        }

        if (!$control->isContextTestable($context)) {
            return new SkippedStepResult($search);
        }

        $call = $this->createDefinitionCall($context, $search);
        $result = $this->callCenter->makeCall($call);

        return new ExecutedStepResult($search, $result);
    }

    /**
     * Creates definition call.
     *
     * @param StepContext  $context
     * @param SearchResult $search
     *
     * @return DefinitionCall
     */
    private function createDefinitionCall(StepContext $context, SearchResult $search)
    {
        return new DefinitionCall(
            $context->getEnvironment(),
            $context->getFeature(),
            $context->getStep(),
            $search->getMatchedDefinition(),
            $search->getMatchedArguments()
        );
    }

    /**
     * Casts provided context to the expected one.
     *
     * @param TestContext $context
     *
     * @return StepContext
     *
     * @throws WrongContextException
     */
    private function castContext(TestContext $context)
    {
        if ($context instanceof StepContext) {
            return $context;
        }

        throw new WrongContextException(
            sprintf(
                'StepTester tests instances of StepContext only, but %s given.',
                get_class($context)
            ), $context
        );
    }
}
