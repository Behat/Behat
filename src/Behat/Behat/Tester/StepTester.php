<?php

namespace Behat\Behat\Tester;

use Symfony\Component\DependencyInjection\ContainerInterface,
    Symfony\Component\EventDispatcher\Event;

use Behat\Gherkin\Node\NodeVisitorInterface,
    Behat\Gherkin\Node\AbstractNode,
    Behat\Gherkin\Node\StepNode,
    Behat\Gherkin\Node\ScenarioNode;

use Behat\Behat\Context\ContextInterface,
    Behat\Behat\Context\Step\SubstepInterface,
    Behat\Behat\Definition\DefinitionInterface,
    Behat\Behat\Exception\AmbiguousException,
    Behat\Behat\Exception\UndefinedException,
    Behat\Behat\Exception\PendingException,
    Behat\Behat\Event\StepEvent;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Step Tester.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class StepTester implements NodeVisitorInterface
{
    private $logicalParent;
    private $dispatcher;
    private $context;
    private $definitions;
    private $skip = false;

    /**
     * Initializes tester.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->dispatcher  = $container->get('behat.event_dispatcher');
        $this->definitions = $container->get('behat.definition.dispatcher');
    }

    /**
     * Sets logical parent of the step, which is always a ScenarioNode.
     *
     * @param ScenarioNode $parent
     */
    public function setLogicalParent(ScenarioNode $parent)
    {
        $this->logicalParent = $parent;
    }

    /**
     * Sets run context.
     *
     * @param ContextInterface $context
     */
    public function setContext(ContextInterface $context)
    {
        $this->context = $context;
    }

    /**
     * Marks test as skipped.
     *
     * @param Boolean $skip skip test?
     */
    public function skip($skip = true)
    {
        $this->skip = $skip;
    }

    /**
     * Visits & tests StepNode.
     *
     * @param AbstractNode $step
     *
     * @return integer
     */
    public function visit(AbstractNode $step)
    {
        $this->dispatcher->dispatch('beforeStep', new StepEvent(
            $step, $this->logicalParent, $this->context
        ));
        $afterEvent = $this->executeStep($step);
        $this->dispatcher->dispatch('afterStep', $afterEvent);

        return $afterEvent->getResult();
    }

    /**
     * Searches and runs provided step with DefinitionDispatcher.
     *
     * @param StepNode $step step node
     *
     * @return StepEvent
     */
    protected function executeStep(StepNode $step)
    {
        $context    = $this->context;
        $result     = null;
        $definition = null;
        $exception  = null;
        $snippet    = null;

        try {
            $definition = $this->definitions->findDefinition($this->context, $step, $this->skip);

            if ($this->skip) {
                return new StepEvent(
                    $step, $this->logicalParent, $context, StepEvent::SKIPPED, $definition
                );
            }

            try {
                $this->executeStepDefinition($step, $definition);
                $result = StepEvent::PASSED;
            } catch (PendingException $e) {
                $result    = StepEvent::PENDING;
                $exception = $e;
            } catch (\Exception $e) {
                $result    = StepEvent::FAILED;
                $exception = $e;
            }
        } catch (UndefinedException $e) {
            $result    = StepEvent::UNDEFINED;
            $snippet   = $this->definitions->proposeDefinition($this->context, $step);
            $exception = $e;
        } catch (\Exception $e) {
            $result    = StepEvent::FAILED;
            $exception = $e;
        }

        return new StepEvent(
            $step, $this->logicalParent, $context, $result, $definition, $exception, $snippet
        );
    }

    /**
     * Executes provided step definition.
     *
     * @param StepNode            $step       step node
     * @param DefinitionInterface $definition step definition
     */
    protected function executeStepDefinition(StepNode $step, DefinitionInterface $definition)
    {
        $this->executeStepsChain($step, $definition->run($this->context));
    }

    /**
     * Executes steps chain (if there's one).
     *
     * @param StepNode $step  step node
     * @param mixed    $chain chain
     *
     * @throws \Exception
     */
    private function executeStepsChain(StepNode $step, $chain = null)
    {
        if (null === $chain) {
            return;
        }

        $chain = is_array($chain) ? $chain : array($chain);
        foreach ($chain as $chainItem) {
            if ($chainItem instanceof SubstepInterface) {
                $substepNode = $chainItem->getStepNode();
                $substepNode->setParent($step->getParent());
                $substepEvent = $this->executeStep($substepNode);

                if (StepEvent::PASSED !== $substepEvent->getResult()) {
                    throw $substepEvent->getException();
                }
            } elseif (is_callable($chainItem)) {
                $this->executeStepsChain($step, call_user_func($chainItem));
            }
        }
    }
}
