<?php

namespace Behat\Behat\Event;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Behat\Behat\Context\Pool\ContextPoolInterface;
use Behat\Behat\Definition\DefinitionInterface;
use Behat\Behat\Snippet\SnippetInterface;
use Behat\Behat\Suite\SuiteInterface;
use Behat\Gherkin\Node\ScenarioNode;
use Behat\Gherkin\Node\StepNode;
use Exception;
use Symfony\Component\EventDispatcher\Event;

/**
 * Step event.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class StepEvent extends Event implements LifecycleEventInterface
{
    const PASSED = 0;
    const SKIPPED = 1;
    const PENDING = 2;
    const UNDEFINED = 3;
    const FAILED = 4;
    /**
     * @var StepNode
     */
    private $step;
    /**
     * @var ScenarioNode
     */
    private $logicalParent;
    /**
     * @var SuiteInterface
     */
    private $suite;
    /**
     * @var ContextPoolInterface
     */
    private $contexts;
    /**
     * @var null|integer
     */
    private $result;
    /**
     * @var DefinitionInterface
     */
    private $definition;
    /**
     * @var Exception
     */
    private $exception;
    /**
     * @var SnippetInterface
     */
    private $snippet;

    /**
     * Initializes step event.
     *
     * @param SuiteInterface           $suite
     * @param ContextPoolInterface     $contexts
     * @param ScenarioNode             $logicalParent
     * @param StepNode                 $step
     * @param null|integer             $result
     * @param null|Exception           $exception
     * @param null|DefinitionInterface $definition
     * @param null|SnippetInterface    $snippet
     */
    public function __construct(
        SuiteInterface $suite,
        ContextPoolInterface $contexts,
        ScenarioNode $logicalParent,
        StepNode $step,
        $result = null,
        Exception $exception = null,
        DefinitionInterface $definition = null,
        SnippetInterface $snippet = null
    )
    {
        $this->suite = $suite;
        $this->contexts = $contexts;
        $this->step = $step;
        $this->logicalParent = $logicalParent;
        $this->result = $result;
        $this->definition = $definition;
        $this->exception = $exception;
        $this->snippet = $snippet;
    }

    /**
     * Returns suite instance.
     *
     * @return SuiteInterface
     */
    public function getSuite()
    {
        return $this->suite;
    }

    /**
     * Returns context pool instance.
     *
     * @return ContextPoolInterface
     */
    public function getContextPool()
    {
        return $this->contexts;
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
        return $this->logicalParent;
    }

    /**
     * Returns step tester result code.
     *
     * @return null|integer
     */
    public function getResult()
    {
        return $this->result;
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
     * Returns step definition object.
     *
     * @return DefinitionInterface
     */
    public function getDefinition()
    {
        return $this->definition;
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
     * Returns step tester exception.
     *
     * @return Exception
     */
    public function getException()
    {
        return $this->exception;
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

    /**
     * Returns step snippet.
     *
     * @return SnippetInterface
     */
    public function getSnippet()
    {
        return $this->snippet;
    }
}
