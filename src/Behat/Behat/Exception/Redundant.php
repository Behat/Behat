<?php

namespace Behat\Behat\Exception;

use Behat\Behat\Definition\Definition;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Redundant exception.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class Redundant extends BehaviorException
{
    /**
     * Initializes redundant exception.
     *
     * @param   Behat\Behat\Definition\Definition   $step2    duplicate step definition
     * @param   Behat\Behat\Definition\Definition   $step1    firstly matched step definition
     */
    public function __construct(Definition $step2, Definition $step1)
    {
        parent::__construct();

        $this->message = sprintf("Step \"%s\" is already defined in %s:%d\n\n%s:%d\n%s:%d",
            $this->step2->getRegex(),
            $this->step1->getFile(), $this->step1->getLine(),
            $this->step1->getFile(), $this->step1->getLine(),
            $this->step2->getFile(), $this->step2->getLine()
        );
    }
}
