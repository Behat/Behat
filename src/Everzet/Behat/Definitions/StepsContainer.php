<?php

namespace Everzet\Behat\Definitions;

use \Everzet\Gherkin\Structures\Step;
use \Everzet\Gherkin\Structures\Inline\PyString;
use \Everzet\Gherkin\Structures\Inline\Table;
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
    protected $undefinedSteps = array();

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
     * Returns undefined steps array
     *
     * @return  array
     */
    public function getUndefinedSteps()
    {
        return $this->undefinedSteps;
    }

    /**
     * Returns snippets for undefined steps
     *
     * @return  string
     */
    public function getUndefinedStepsSnippets()
    {
        $snippets = '';

        foreach ($this->undefinedSteps as $id => $regexp) {
            $snippets .= sprintf("\n\n%s", $regexp);
        }

        return $snippets;
    }

    /**
     * Translates step description into definition
     *
     * @param   Step    $step   step instance
     * @param   string  $text   step test
     * 
     * @return  string          definition
     */
    protected function convertDescriptionToDefinition($step, $text)
    {
        $regexp = preg_replace(
            array('/\"([^\"]*)\"/', '/(\d+)/'), array("\"([^\"]*)\"", "(\\d+)"), $text, -1, $count
        );
        $args = array();
        for ($i = 0; $i < $count; $i++) {
            $args[] = "\$arg".($i + 1);
        }
        foreach ($step->getArguments() as $argument) {
            if ($argument instanceof PyString) {
                $args[] = "\$string";
            } elseif ($argument instanceof Table) {
                $args[] = "\$table";
            }
        }

        return sprintf("\$steps->%s('/^%s$/', function(%s) use(\$world) {\n    throw new \Everzet\Behat\Exceptions\Pending;\n});",
            '%s', $regexp, implode(', ', $args)
        );
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
            $regexp = $this->convertDescriptionToDefinition($step, $text);
            if (!isset($this->undefinedSteps[$regexp])) {
                $this->undefinedSteps[$regexp] = sprintf($regexp, $step->getType());
            }
            throw new Undefined($text);
        }

        return $matches[0];
    }
}
