<?php

namespace Behat\Behat\Definition;

use Behat\Gherkin\Node\StepNode;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Definition snippet.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class DefinitionSnippet
{
    private $type;
    private $template;
    private $steps = array();

    /**
     * Initializes definition snippet.
     *
     * @param StepNode $step     Step interested in snippet
     * @param string   $template Definition snippet template
     */
    public function __construct(StepNode $step, $template)
    {
        $type           = $step->getType();
        $this->type     = in_array($type, array('Given', 'When', 'Then')) ? $type : 'Given';
        $this->template = $template;
        $this->steps[]  = $step;
    }

    /**
     * Adds step interested in this snippet.
     *
     * @param StepNode $step Step interested in snippet
     */
    public function addStep(StepNode $step)
    {
        $this->steps[] = $step;
    }

    /**
     * Returns last step in list of steps.
     *
     * @return StepNode
     */
    public function getLastStep()
    {
        return end($this->steps);
    }

    /**
     * Returns list of steps interested in this snippet.
     *
     * @return array
     */
    public function getSteps()
    {
        return $this->steps;
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
        return sprintf($this->template, $this->type);
    }

    /**
     * Returns definition snippet text.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getSnippet();
    }
}
