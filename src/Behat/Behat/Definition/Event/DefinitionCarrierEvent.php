<?php

namespace Behat\Behat\Definition\Event;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Behat\Behat\Context\Pool\ContextPoolInterface;
use Behat\Behat\Definition\DefinitionInterface;
use Behat\Behat\Event\LifecycleEventInterface;
use Behat\Behat\Suite\SuiteInterface;
use Behat\Gherkin\Node\StepNode;
use Symfony\Component\EventDispatcher\Event;

/**
 * Definition carrier event.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class DefinitionCarrierEvent extends Event implements LifecycleEventInterface
{
    /**
     * @var SuiteInterface
     */
    private $suite;
    /**
     * @var ContextPoolInterface
     */
    private $contexts;
    /**
     * @var StepNode
     */
    private $step;
    /**
     * @var DefinitionInterface
     */
    private $definition;
    /**
     * @var string
     */
    private $matchedText;
    /**
     * @var array
     */
    private $arguments = array();

    /**
     * Initializes event.
     *
     * @param SuiteInterface       $suite
     * @param ContextPoolInterface $contexts
     * @param StepNode             $step
     */
    public function __construct(SuiteInterface $suite, ContextPoolInterface $contexts, StepNode $step)
    {
        $this->suite = $suite;
        $this->contexts = $contexts;
        $this->step = $step;
    }

    /**
     * Returns suite.
     *
     * @return SuiteInterface
     */
    public function getSuite()
    {
        return $this->suite;
    }

    /**
     * Returns context pool.
     *
     * @return ContextPoolInterface
     */
    public function getContextPool()
    {
        return $this->contexts;
    }

    /**
     * Returns step.
     *
     * @return StepNode
     */
    public function getStep()
    {
        return $this->step;
    }

    /**
     * Checks if carrier already has definition.
     *
     * @return Boolean
     */
    public function hasDefinition()
    {
        return null !== $this->definition;
    }

    /**
     * Returns carrying definition (if has one).
     *
     * @return DefinitionInterface
     */
    public function getDefinition()
    {
        return $this->definition;
    }

    /**
     * Sets definition to carry.
     *
     * @param DefinitionInterface $definition
     */
    public function setDefinition(DefinitionInterface $definition)
    {
        $this->definition = $definition;
    }

    /**
     * Returns matched text.
     *
     * @return string
     */
    public function getMatchedText()
    {
        return $this->matchedText;
    }

    /**
     * Sets matched text.
     *
     * @param string $text
     */
    public function setMatchedText($text)
    {
        $this->matchedText = $text;
    }

    /**
     * Returns arguments for found definition.
     *
     * @return array
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     * Sets arguments.
     *
     * @param array $arguments
     */
    public function setArguments(array $arguments)
    {
        $this->arguments = $arguments;
    }
}
