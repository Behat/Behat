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
use Behat\Gherkin\Node\ScenarioInterface;
use Behat\Gherkin\Node\StepContainerInterface;
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
     * @var ScenarioInterface
     */
    private $scenario;
    /**
     * @var StepContainerInterface
     */
    private $container;
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
    private $status;
    /**
     * @var null|string
     */
    private $stdOut;
    /**
     * @var null|DefinitionInterface
     */
    private $definition;
    /**
     * @var null|Exception
     */
    private $exception;
    /**
     * @var null|SnippetInterface
     */
    private $snippet;

    /**
     * Initializes step event.
     *
     * @param SuiteInterface           $suite
     * @param ContextPoolInterface     $contexts
     * @param ScenarioInterface        $scenario
     * @param StepContainerInterface   $container
     * @param StepNode                 $step
     * @param null|integer             $status
     * @param null|string              $stdOut
     * @param null|Exception           $exception
     * @param null|DefinitionInterface $definition
     * @param null|SnippetInterface    $snippet
     */
    public function __construct(
        SuiteInterface $suite,
        ContextPoolInterface $contexts,
        ScenarioInterface $scenario,
        StepContainerInterface $container,
        StepNode $step,
        $status = null,
        $stdOut = null,
        Exception $exception = null,
        DefinitionInterface $definition = null,
        SnippetInterface $snippet = null
    )
    {
        $this->suite = $suite;
        $this->contexts = $contexts;
        $this->step = $step;
        $this->scenario = $scenario;
        $this->container = $container;
        $this->status = $status;
        $this->stdOut = $stdOut;
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
     * Returns logical scenario to the step.
     *
     * - For scenario, this would be a scenario itself
     * - For outline example, this would be an outline of that example
     * - For scenario background, this would be a scenario this background was runned for
     * - For outline background, this would be an outline this background was runned for
     *
     * @return ScenarioInterface
     */
    public function getScenario()
    {
        return $this->scenario;
    }

    /**
     * Returns logical step container this step belongs to.
     *
     * - For scenario, this would be a scenario itself
     * - For outline example, this would be an outline example itself
     * - For scenario background, this would be a scenario this background was runned for
     * - For outline background step, this would be an outline example this background was runned for
     *
     * @return StepContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
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
     * @return null|DefinitionInterface
     */
    public function getDefinition()
    {
        return $this->definition;
    }

    /**
     * Returns step tester result status.
     *
     * @return null|integer
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Checks if standard output was produced during event.
     *
     * @return Boolean
     */
    public function hasStdOut()
    {
        return null !== $this->stdOut;
    }

    /**
     * Returns standard output produced during event.
     *
     * @return null|string
     */
    public function getStdOut()
    {
        return $this->stdOut;
    }

    /**
     * Checks whether event contains exception.
     *
     * @return Boolean
     */
    public function hasException()
    {
        return null !== $this->exception;
    }

    /**
     * Returns step tester exception.
     *
     * @return null|Exception
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
        return null !== $this->snippet;
    }

    /**
     * Returns step snippet.
     *
     * @return null|SnippetInterface
     */
    public function getSnippet()
    {
        return $this->snippet;
    }
}
