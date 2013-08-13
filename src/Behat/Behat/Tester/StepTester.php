<?php

namespace Behat\Behat\Tester;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Behat\Behat\Callee\Event\ExecuteCalleeEvent;
use Behat\Behat\Context\Pool\ContextPoolInterface;
use Behat\Behat\Definition\Event\DefinitionCarrierEvent;
use Behat\Behat\Definition\Event\ExecuteDefinitionEvent;
use Behat\Behat\Event\EventInterface;
use Behat\Behat\Event\StepEvent;
use Behat\Behat\EventDispatcher\DispatchingService;
use Behat\Behat\Exception\PendingException;
use Behat\Behat\Exception\UndefinedException;
use Behat\Behat\Snippet\Event\SnippetCarrierEvent;
use Behat\Behat\Snippet\SnippetInterface;
use Behat\Behat\Suite\SuiteInterface;
use Behat\Gherkin\Node\ScenarioNode;
use Behat\Gherkin\Node\StepNode;
use Exception;

/**
 * Step DispatchingTester.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class StepTester extends DispatchingService
{
    /**
     * Tests step.
     *
     * @param SuiteInterface       $suite
     * @param ContextPoolInterface $contexts
     * @param StepNode             $step
     * @param ScenarioNode         $scenario
     *
     * @return integer
     */
    public function test(
        SuiteInterface $suite,
        ContextPoolInterface $contexts,
        StepNode $step,
        ScenarioNode $scenario
    )
    {
        $event = new StepEvent($suite, $contexts, $scenario, $step);
        $this->dispatch(EventInterface::BEFORE_STEP, $event);

        $result = StepEvent::PASSED;
        $execution = $exception = $snippet = null;

        try {
            $execution = $this->getExecutionEvent($suite, $contexts, $step);
            $this->dispatch(EventInterface::EXECUTE_DEFINITION, $execution);
        } catch (PendingException $e) {
            $result = StepEvent::PENDING;
            $exception = $e;
        } catch (UndefinedException $e) {
            $result = StepEvent::UNDEFINED;
            $exception = $e;
            $snippet = $this->getDefinitionSnippet($suite, $contexts, $step);
        } catch (Exception $e) {
            $result = StepEvent::FAILED;
            $exception = $e;
        }

        $definition = $execution ? $execution->getCallee() : null;

        $event = new StepEvent(
            $suite, $contexts, $scenario, $step, $result, $exception, $definition, $snippet
        );
        $this->dispatch(EventInterface::AFTER_STEP, $event);

        return $result;
    }

    /**
     * Returns execution event.
     *
     * @param SuiteInterface       $suite
     * @param ContextPoolInterface $contexts
     * @param StepNode             $step
     *
     * @return ExecuteCalleeEvent
     *
     * @throws UndefinedException
     */
    protected function getExecutionEvent(
        SuiteInterface $suite,
        ContextPoolInterface $contexts,
        StepNode $step
    )
    {
        $definitionProvider = new DefinitionCarrierEvent($suite, $contexts, $step);
        $this->dispatch(EventInterface::FIND_DEFINITION, $definitionProvider);

        if (!$definitionProvider->hasDefinition()) {
            throw new UndefinedException($step->getText());
        }

        $definition = $definitionProvider->getDefinition();
        $arguments = $definitionProvider->getArguments();

        return new ExecuteDefinitionEvent($suite, $contexts, $step, $definition, $arguments);
    }

    /**
     * Returns definition snippet.
     *
     * @param SuiteInterface       $suite
     * @param ContextPoolInterface $contexts
     * @param StepNode             $step
     *
     * @return SnippetInterface
     */
    protected function getDefinitionSnippet(
        SuiteInterface $suite,
        ContextPoolInterface $contexts,
        StepNode $step
    )
    {
        $snippetProvider = new SnippetCarrierEvent($suite, $contexts, $step);
        $this->dispatch(EventInterface::CREATE_SNIPPET, $snippetProvider);

        return $snippetProvider->getSnippet();
    }
}
