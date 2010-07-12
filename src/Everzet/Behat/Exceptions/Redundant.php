<?php

namespace Everzet\Behat\Exceptions;

use \Everzet\Behat\StepDefinition;

class Redundant extends BehaviorException
{
    protected $step1;
    protected $step2;

    public function __construct(StepDefinition $step2, StepDefinition $step1)
    {
        $this->step1 = $step1;
        $this->step2 = $step2;

        parent::__construct();
    }

    public function __toString()
    {
        return sprintf("Step \"%s\" is already defined in %s:%d\n\n%s:%d\n%s:%d",
            $this->step2->getRegex(), $this->step1->getFile(), $this->step1->getLine(),

            $this->step1->getFile(), $this->step1->getLine(),
            $this->step2->getFile(), $this->step2->getLine()
        );
    }
}