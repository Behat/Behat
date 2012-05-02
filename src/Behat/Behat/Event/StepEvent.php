<?php

namespace Behat\Behat\Event;

use Symfony\Component\EventDispatcher\Event;

use Behat\Behat\Context\ContextInterface,
    Behat\Behat\Definition\DefinitionInterface,
    Behat\Behat\Definition\DefinitionSnippet;

use Behat\Gherkin\Node\StepNode,
    Behat\Gherkin\Node\ScenarioNode;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Step event.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class StepEvent extends Event implements EventInterface
{
    const PASSED    = 0;
    const SKIPPED   = 1;
    const PENDING   = 2;
    const UNDEFINED = 3;
    const FAILED    = 4;

    private $step;
    private $parent;
    private $context;
    private $result;
    private $definition;
    private $exception;
    private $snippet;

    /**
     * Initializes step event.
     *
     * @param StepNode            $step
     * @param ScenarioNode        $parent
     * @param ContextInterface    $context
     * @param integer             $result
     * @param DefinitionInterface $definition
     * @param \Exception          $exception
     * @param DefinitionSnippet   $snippet
     */
    public function __construct(StepNode $step, ScenarioNode $parent, ContextInterface $context,
                                $result = null, DefinitionInterface $definition = null,
                                \Exception $exception = null, DefinitionSnippet $snippet = null)
    {
        $this->step       = $step;
        $this->parent     = $parent;
        $this->context    = $context;
        $this->result     = $result;
        $this->definition = $definition;
        $this->exception  = $exception;
        $this->snippet    = $snippet;
    }

    /**
     * Returns step node.
     *
     * @return StepNode
     */
    public function getStep()
    {
        return $this->step;
    }

    /**
     * Returns logical parent to the step, which is always a ScenarioNode.
     *
     * @return ScenarioNode
     */
    public function getLogicalParent()
    {
        return $this->parent;
    }

    /**
     * Returns context object.
     *
     * @return ContextInterface
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Returns step tester result code.
     *
     * @return integer
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * Returns step definition object.
     *
     * @return DefinitionInterface
     */
    public function getDefinition()
    {
        return $this->definition;
    }

    /**
     * Checks whether event contains step definition.
     *
     * @return Boolean
     */
    public function hasDefinition()
    {
        return null !== $this->getDefinition();
    }

    /**
     * Returns step tester exception.
     *
     * @return \Exception
     */
    public function getException()
    {
        return $this->exception;
    }

    /**
     * Checks whether event contains exception.
     *
     * @return Boolean
     */
    public function hasException()
    {
        return null !== $this->getException();
    }

    /**
     * Returns step snippet.
     *
     * @return DefinitionSnippet
     */
    public function getSnippet()
    {
        return $this->snippet;
    }

    /**
     * Checks whether event contains snippet.
     *
     * @return Boolean
     */
    public function hasSnippet()
    {
        return null !== $this->getSnippet();
    }
}
