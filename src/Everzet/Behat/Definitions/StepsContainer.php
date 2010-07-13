<?php

namespace Everzet\Behat\Definitions;

use \Everzet\Gherkin\Step;
use \Everzet\Behat\Definitions\StepDefinition;
use \Everzet\Behat\Exceptions\Redundant;
use \Everzet\Behat\Exceptions\Ambiguous;
use \Everzet\Behat\Exceptions\Undefined;

/*
 * This file is part of the behat package.
 * (c) 2010 Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Steps Container
 *
 * @package     behat
 * @subpackage  Behat
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class StepsContainer
{
    protected $steps = array();

    /**
     * Define a step with ->Given('/regex/', callback)
     *
     * @param   string  $type       step type (Given/When/Then/And or localized one)
     * @param   string  $arguments  step regex & callback
     * 
     * @throws  \Everzet\Behat\Exceptions\Redundant if step definition is already exists
     */
    public function __call($type, $arguments)
    {
        $debug = debug_backtrace();
        $debug = $debug[1];

        $definition = new StepDefinition(
            $type, $arguments[0], $arguments[1], $debug['file'], $debug['line']
        );

        if (isset($this->steps[$definition->getRegex()])) {
            throw new Redundant(
                $definition, $this->steps[$definition->getRegex()]
            );
        }

        $this->steps[$definition->getRegex()] = $definition;

        return $this;
    }

    /**
     * Finds & returns step definition, that matches specific step description
     *
     * @param   Step            $step       specific step to match
     * @param   array           $examples   examples tokens to replace description placeholders
     * 
     * @return  StepDefinition
     * 
     * @throws  \Everzet\Behat\Exceptions\Ambiguous if step description is ambiguous
     * @throws  \Everzet\Behat\Exceptions\Undefined if step definition not found
     */
    public function findDefinition(Step $step, array $examples = array())
    {
        $text = $step->getText($examples);
        $matches = array();

        foreach ($this->steps as $regex => $definition) {
            if (preg_match($regex, $text, $values)) {
                $definition->setMatchedText($text);
                $definition->setValues(array_merge(array_slice($values, 1), $step->getArguments()));
                $matches[] = $definition;
            }
        }

        if (count($matches) > 1) {
            throw new Ambiguous($text, $matches);
        }

        if (0 === count($matches)) {
            throw new Undefined($text);
        }

        return $matches[0];
    }
}
