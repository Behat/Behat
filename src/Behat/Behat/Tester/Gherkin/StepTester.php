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
use Behat\Testwork\Tester\Context\Context;
use Behat\Testwork\Tester\Exception\WrongContextException;
use Behat\Testwork\Tester\RunControl;
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
    public function test(Context $context, RunControl $control)
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
     * @param StepContext $ctx
     *
     * @return SearchResult
     */
    private function searchDefinition(StepContext $ctx)
    {
        return $this->definitionFinder->findDefinition(
            $ctx->getEnvironment(),
            $ctx->getFeature(),
            $ctx->getStep()
        );
    }

    /**
     * Tests found definition.
     *
     * @param StepContext  $ctx
     * @param RunControl   $ctrl
     * @param SearchResult $search
     *
     * @return StepResult
     */
    private function testDefinition(StepContext $ctx, RunControl $ctrl, SearchResult $search)
    {
        if (!$search->hasMatch()) {
            return new UndefinedStepResult();
        }

        if ($ctrl->isSkip()) {
            return new SkippedStepResult($search);
        }

        $call = $this->createDefinitionCall($ctx, $search);
        $result = $this->callCenter->makeCall($call);

        return new ExecutedStepResult($search, $result);
    }

    /**
     * Creates definition call.
     *
     * @param StepContext  $ctx
     * @param SearchResult $search
     *
     * @return DefinitionCall
     */
    private function createDefinitionCall(StepContext $ctx, SearchResult $search)
    {
        $definition = $search->getMatchedDefinition();
        $arguments = $search->getMatchedArguments();

        return new DefinitionCall(
            $ctx->getEnvironment(), $ctx->getFeature(), $ctx->getStep(),
            $definition,
            $arguments
        );
    }

    /**
     * Casts provided context to the expected one.
     *
     * @param Context $context
     *
     * @return StepContext
     *
     * @throws WrongContextException
     */
    private function castContext(Context $context)
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
