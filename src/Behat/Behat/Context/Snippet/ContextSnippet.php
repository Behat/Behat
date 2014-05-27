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
     * @var StepNode
     */
    private $step;
    /**
     * @var string
     */
    private $template;
    /**
     * @var string
     */
    private $contextClass;

    /**
     * Initializes definition snippet.
     *
     * @param StepNode $step
     * @param string   $template
     * @param string   $contextClass
     */
    public function __construct(StepNode $step, $template, $contextClass)
    {
        $this->step = $step;
        $this->template = $template;
        $this->contextClass = $contextClass;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return 'context';
    }

    /**
     * {@inheritdoc}
     */
    public function getHash()
    {
        return md5($this->template);
    }

    /**
     * {@inheritdoc}
     */
    public function getSnippet()
    {
        return sprintf($this->template, $this->step->getKeywordType());
    }

    /**
     * {@inheritdoc}
     */
    public function getStep()
    {
        return $this->step;
    }

    /**
     * {@inheritdoc}
     */
    public function getTarget()
    {
        return $this->contextClass;
    }
}
