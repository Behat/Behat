<?php

namespace Everzet\Behat\Exceptions;

use \Everzet\Behat\StepDefinition;

/*
 * This file is part of the behat package.
 * (c) 2010 Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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