<?php

namespace BehaviorTester\Exceptions;

class Redundant extends BehaviorException
{
    protected $step1;
    protected $step2;

    public function __construct($step2, $step1)
    {
        $this->step1 = $step1;
        $this->step2 = $step2;

        parent::__construct();
    }

    public function __toString()
    {
        return sprintf("Step \"%s\" is already defined in %s:%d\n\n%s:%d\n%s:%d",
            $this->step2['step'], $this->step1['file'], $this->step1['line'],

            $this->step1['file'], $this->step1['line'],
            $this->step2['file'], $this->step2['line']
        );
    }
}