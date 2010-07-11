<?php

namespace BehaviorTester;

class StepsDefinition
{
    protected $steps = array();

    public function __call($adverb, $arguments)
    {
        $debug = debug_backtrace();
        $debug = $debug[1];

        $stepInfo = array(
            'type'      => $adverb,
            'step'      => $arguments[0],
            'callback'  => $arguments[1],
            'file'      => $debug['file'],
            'line'      => $debug['line']
        );

        if (isset($this->steps[$stepInfo['step']])) {
            throw new \BehaviorTester\Exceptions\Redundant(
                $stepInfo, $this->steps[$stepInfo['step']]
            );
        }

        $this->steps[$stepInfo['step']] = $stepInfo;

        return $this;
    }

    public function findDefinition(\Gherkin\Step $step, array $examples = array())
    {
        $description = $step->getText($examples);
        $matches = array();

        foreach ($this->steps as $regex => $params) {
            if (preg_match($regex, $description, $values)) {
                $matches[] = array_merge($params, array(
                    'values'        => array_merge(array_slice($values, 1), $step->getArguments()),
                    'description'   => $description
                ));
            }
        }

        if (count($matches) > 1) {
            throw new \BehaviorTester\Exceptions\Ambiguous($description, $matches);
        }

        if (0 === count($matches)) {
            throw new \BehaviorTester\Exceptions\Undefined($description);
        }

        return $matches[0];
    }
}
