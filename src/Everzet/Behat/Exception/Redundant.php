<?php

namespace Everzet\Behat\Exception;

use Everzet\Behat\StepDefinition\Definition;
use Everzet\Behat\Output\Formatter\ConsoleFormatter as Formatter;

/*
 * This file is part of the Behat.
 * (c) 2010 Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Redundant Exception.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class Redundant extends BehaviorException
{
    protected $step1;
    protected $step2;

    /**
     * Initialize Exception.
     *
     * @param   Definition  $step2  duplicate step definition
     * @param   Definition  $step1  firstly matched step definition
     */
    public function __construct(Definition $step2, Definition $step1)
    {
        parent::__construct();

        $this->step1 = $step1;
        $this->step2 = $step2;
        $this->message = sprintf("Step \"%s\" is already defined in %s:%d\n\n%s:%d\n%s:%d",
            $this->step2->getRegex(), Formatter::trimFilename($this->step1->getFile()), $this->step1->getLine()
          , Formatter::trimFilename($this->step1->getFile()), $this->step1->getLine()
          , Formatter::trimFilename($this->step2->getFile()), $this->step2->getLine()
        );
    }
}
