<?php

namespace Behat\Behat\Snippet\Event;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Behat\Behat\Context\Pool\ContextPoolInterface;
use Behat\Behat\Event\LifecycleEventInterface;
use Behat\Behat\Snippet\SnippetInterface;
use Behat\Behat\Suite\SuiteInterface;
use Behat\Gherkin\Node\StepNode;
use Symfony\Component\EventDispatcher\Event;

/**
 * Snippet carrier event.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class SnippetCarrierEvent extends Event implements LifecycleEventInterface
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
     * @var null|SnippetInterface
     */
    private $snippet;

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
        $this->step = $step;
        $this->contexts = $contexts;
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
     * Returns step node.
     *
     * @return StepNode
     */
    public function getStep()
    {
        return $this->step;
    }

    /**
     * Checks if carrier has snippet.
     *
     * @return Boolean
     */
    public function hasSnippet()
    {
        return null !== $this->snippet;
    }

    /**
     * Returns snippet.
     *
     * @return null|SnippetInterface
     */
    public function getSnippet()
    {
        return $this->snippet;
    }

    /**
     * Sets snippet into carrier.
     *
     * @param SnippetInterface $snippet
     */
    public function setSnippet(SnippetInterface $snippet)
    {
        $this->snippet = $snippet;
    }
}
