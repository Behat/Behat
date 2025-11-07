<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Context\Snippet;

use Behat\Behat\Snippet\Snippet;
use Behat\Gherkin\Node\StepNode;

/**
 * Represents a definition snippet for a context class.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class ContextSnippet implements Snippet
{
    /**
     * Initializes definition snippet.
     *
     * @param string[] $usedClasses
     */
    public function __construct(
        private readonly StepNode $step,
        private readonly string $template,
        private readonly string $contextClass,
        private readonly array $usedClasses = [],
    ) {
    }

    public function getType(): string
    {
        return 'context';
    }

    public function getHash(): string
    {
        return md5($this->template);
    }

    public function getSnippet(): string
    {
        return sprintf($this->template, $this->step->getKeywordType());
    }

    public function getStep(): StepNode
    {
        return $this->step;
    }

    public function getTarget(): string
    {
        return $this->contextClass;
    }

    /**
     * Returns the classes used in the snippet which should be imported.
     *
     * @return string[]
     */
    public function getUsedClasses(): array
    {
        return $this->usedClasses;
    }
}
