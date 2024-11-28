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
    private StepNode $step;

    private string $template;

    private string $contextClass;

    /**
     * @var string[]
     */
    private array $usedClasses;

    /**
     * Initializes definition snippet.
     *
     * @param string[] $usedClasses
     */
    public function __construct(StepNode $step, string $template, string $contextClass, array $usedClasses = [])
    {
        $this->step = $step;
        $this->template = $template;
        $this->contextClass = $contextClass;
        $this->usedClasses = $usedClasses;
    }

    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return 'context';
    }

    /**
     * {@inheritdoc}
     */
    public function getHash(): string
    {
        return md5($this->template);
    }

    /**
     * {@inheritdoc}
     */
    public function getSnippet(): string
    {
        return sprintf($this->template, $this->step->getKeywordType());
    }

    /**
     * {@inheritdoc}
     */
    public function getStep(): StepNode
    {
        return $this->step;
    }

    /**
     * {@inheritdoc}
     */
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
