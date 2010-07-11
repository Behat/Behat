<?php

namespace BehaviorTester\Exceptions;

class Ambiguous extends BehaviorException
{
    protected $definition;
    protected $matches = array();

    public function __construct($definition, array $matches)
    {
        $this->definition = $definition;
        $this->matches = $matches;

        parent::__construct();
    }

    public function __toString()
    {
        $string = sprintf("Ambiguous match of \"%s\":", $this->definition);

        foreach ($this->matches as $match){
            $string .= sprintf("\n%s:%d:in `%s`", $match['file'], $match['line'], $match['step']);
        }

        return $string;
    }
}