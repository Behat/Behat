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
 * Context-based step definition snippet.
 *
 * Represents context step snippet.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class ContextSnippet implements Snippet
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
     * @var string[]
     */
    private $contextClass = array();

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
     * Returns snippet type.
     *
     * @return string
     */
    public function getType()
    {
        return 'context';
    }

    /**
     * Returns snippet unique hash (ignoring step type).
     *
     * @return string
     */
    public function getHash()
    {
        return md5($this->template);
    }

    /**
     * Returns definition snippet text.
     *
     * @return string
     */
    public function getSnippet()
    {
        $type = in_array($this->step->getType(), array('Given', 'When', 'Then')) ? $this->step->getType() : 'Given';

        return sprintf($this->template, $type);
    }

    /**
     * Returns step which asked for this snippet.
     *
     * @return StepNode
     */
    public function getStep()
    {
        return $this->step;
    }

    /**
     * Returns text representations of snippet targets (for printing).
     *
     * @return string
     */
    public function getTarget()
    {
        return $this->contextClass;
    }
}
