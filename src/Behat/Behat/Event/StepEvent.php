<?php

namespace Behat\Behat\Event;

use Symfony\Component\EventDispatcher\Event;

use Behat\Behat\Context\ContextInterface,
    Behat\Behat\Definition\DefinitionInterface,
    Behat\Behat\Definition\DefinitionSnippet;

use Behat\Gherkin\Node\StepNode;

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
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class StepEvent extends Event implements EventInterface
{
    const PASSED    = 0;
    const UNSTABLE  = 1;
    const SKIPPED   = 2;
    const PENDING   = 3;
    const UNDEFINED = 4;
    const FAILED    = 5;

    private $step;
    private $context;
    private $result;
    private $definition;
    private $exception;
    private $snippet;

    /**
     * Initializes step event.
     *
     * @param   Behat\Gherkin\Node\StepNode                 $step
     * @param   Behat\Behat\Context\ContextInterface        $context
     * @param   integer                                     $result
     * @param   Behat\Behat\Definition\DefinitionInterface  $definition
     * @param   Exception                                   $exception
     * @param   Behat\Behat\Definition\DefinitionSnippet    $snippet
     */
    public function __construct(StepNode $step, ContextInterface $context, $result = null,
                                DefinitionInterface $definition = null, \Exception $exception = null,
                                DefinitionSnippet $snippet = null)
    {
        $this->step          = $step;
        $this->context       = $context;
        $this->result        = $result;
        $this->definition    = $definition;
        $this->exception     = $exception;
        $this->snippet       = $snippet;
    }

    /**
     * Returns step node.
     *
     * @return  Behat\Gherkin\Node\StepNode
     */
    public function getStep()
    {
        return $this->step;
    }

    /**
     * Returns context object.
     *
     * @return  Behat\Behat\Context\ContextInterface
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Returns step tester result code.
     *
     * @return  integer
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * Returns step definition object.
     *
     * @return  Behat\Behat\Definition\Definition
     */
    public function getDefinition()
    {
        return $this->definition;
    }

    /**
     * Checks whether event contains step definition.
     *
     * @return  Boolean
     */
    public function hasDefinition()
    {
        return null !== $this->getDefinition();
    }

    /**
     * Returns step tester exception.
     *
     * @return  Exception
     */
    public function getException()
    {
        return $this->exception;
    }

    /**
     * Checks whether event contains exception.
     *
     * @return  Boolean
     */
    public function hasException()
    {
        return null !== $this->getException();
    }

    /**
     * Returns step snippet.
     *
     * @return  Behat\Behat\Definition\DefinitionSnippet
     */
    public function getSnippet()
    {
        return $this->snippet;
    }

    /**
     * Checks whether event contains snippet.
     *
     * @return  Boolean
     */
    public function hasSnippet()
    {
        return null !== $this->getSnippet();
    }
}
