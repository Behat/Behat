<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Snippet;

use Behat\Behat\Context\Snippet\ContextSnippet;
use Behat\Gherkin\Node\StepNode;

/**
 * Aggregates multiple similar snippets with different targets and steps.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class AggregateSnippet
{
    /**
     * @var Snippet[]
     */
    private array $snippets;

    /**
     * Initializes snippet.
     *
     * @param Snippet[] $snippets
     */
    public function __construct(array $snippets)
    {
        $this->snippets = $snippets;
    }

    /**
     * Returns snippet type.
     */
    public function getType(): string
    {
        return current($this->snippets)->getType();
    }

    /**
     * Returns snippet unique ID (step type independent).
     */
    public function getHash(): string
    {
        return current($this->snippets)->getHash();
    }

    /**
     * Returns definition snippet text.
     */
    public function getSnippet(): string
    {
        return current($this->snippets)->getSnippet();
    }

    /**
     * Returns all steps interested in this snippet.
     *
     * @return StepNode[]
     */
    public function getSteps(): array
    {
        return array_unique(
            array_map(
                function (Snippet $snippet) {
                    return $snippet->getStep();
                },
                $this->snippets
            ),
            SORT_REGULAR
        );
    }

    /**
     * Returns all snippet targets.
     *
     * @return string[]
     */
    public function getTargets(): array
    {
        return array_unique(
            array_map(
                function (Snippet $snippet) {
                    return $snippet->getTarget();
                },
                $this->snippets
            )
        );
    }

    /**
     * Returns the classes used in the snippet which should be imported.
     *
     * @return string[]
     */
    public function getUsedClasses(): array
    {
        if (empty($this->snippets)) {
            return array();
        }

        return array_unique(
            array_merge(
                ...array_values(
                    array_map(
                        function (Snippet $snippet) {
                            if (!$snippet instanceof ContextSnippet) {
                                return array();
                            }

                            return $snippet->getUsedClasses();
                        },
                        $this->snippets
                    )
                )
            )
        );
    }
}
