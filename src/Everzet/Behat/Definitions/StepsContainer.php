<?php

namespace Everzet\Behat\Definitions;

use \Everzet\Gherkin\Step;
use \Everzet\Behat\Definitions\StepDefinition;
use \Everzet\Behat\Exceptions\Redundant;
use \Everzet\Behat\Exceptions\Ambiguous;
use \Everzet\Behat\Exceptions\Undefined;

class StepsContainer
{
    protected $steps = array();

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

    public function findDefinition(Step $step, array $examples = array())
    {
        $text = $step->getText($examples);
        $matches = array();

        foreach ($this->steps as $regex => $definition) {
            if (preg_match($regex, $text, $values)) {
                $definition->setMatchedText($text);
                $definition->addValues(array_merge(array_slice($values, 1), $step->getArguments()));
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
