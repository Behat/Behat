<?php

namespace Behat\Behat\Snippet;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Behat\Gherkin\Node\StepNode;
use InvalidArgumentException;

/**
 * Snippets repository interface.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface RepositoryInterface
{
    /**
     * Registers step snippet.
     *
     * @param StepNode         $step
     * @param SnippetInterface $snippet
     */
    public function registerSnippet(StepNode $step, SnippetInterface $snippet);

    /**
     * Check if some snippet been registered.
     *
     * @return Boolean
     */
    public function hasSnippets();

    /**
     * Returns registered snippets.
     *
     * @return SnippetInterface[]
     */
    public function getSnippets();

    /**
     * Returns list of steps that need snippet.
     *
     * @param SnippetInterface $snippet
     *
     * @return StepNode[]
     *
     * @throws InvalidArgumentException If snippet was not registered
     */
    public function getStepsThatNeedSnippet(SnippetInterface $snippet);
}
