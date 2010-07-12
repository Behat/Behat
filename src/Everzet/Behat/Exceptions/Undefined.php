<?php

namespace Everzet\Behat\Exceptions;

class Undefined extends BehaviorException
{
    protected $step;

    public function __construct($step)
    {
        $this->step = $step;

        parent::__construct();
    }

    public function __toString()
    {
        return sprintf('Undefined step "%s"', $this->step);
    }
}