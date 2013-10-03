<?php

namespace Behat\Behat\Snippet;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Behat\Behat\Snippet\RepositoryInterface;
use Behat\Behat\Snippet\SnippetInterface;
use Behat\Gherkin\Node\StepNode;

/**
 * Snippets repository.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class ContextSnippetRepository implements RepositoryInterface
{
    /**
     * @var SnippetInterface[]
     */
    private $snippets = array();
    /**
     * @var StepNode[]
     */
    private $snippetSteps = array();

    /**
     * Registers step snippet.
     *
     * @param StepNode         $step
     * @param SnippetInterface $snippet
     */
    public function registerSnippet(StepNode $step, SnippetInterface $snippet)
    {
        $hash = $snippet->getHash();

        if (!isset($this->snippets[$hash])) {
            $this->snippets[$hash] = $snippet;
            $this->snippetSteps[$hash] = array();
        }

        $this->snippets[$hash]->merge($snippet);
        $this->snippetSteps[$hash][] = $step;
    }

    /**
     * Check if some snippet been collected.
     *
     * @return Boolean
     */
    public function hasSnippets()
    {
        return count($this->snippets) > 0;
    }

    /**
     * Returns hash of definition snippets for undefined steps.
     *
     * @return SnippetInterface[]
     */
    public function getSnippets()
    {
        return $this->snippets;
    }

    /**
     * Returns list of steps that need this exact snippet.
     *
     * @param SnippetInterface $snippet
     *
     * @return StepNode[]
     */
    public function getStepsThatNeedSnippet(SnippetInterface $snippet)
    {
        return $this->snippetSteps[$snippet->getHash()];
    }
}
