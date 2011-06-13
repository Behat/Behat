<?php

namespace Behat\Behat\Exception;

use Behat\Behat\Definition\DefinitionInterface;

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
     * @param   Behat\Behat\Definition\DefinitionInterface  $step2    duplicate step definition
     * @param   Behat\Behat\Definition\DefinitionInterface  $step1    firstly matched step definition
     */
    public function __construct(DefinitionInterface $step2, DefinitionInterface $step1)
    {
        $message = sprintf("Step \"%s\" is already defined in %s::%s()\n\n%s::%s()\n%s::%s()",
            $step2->getRegex(),
            $step1->getClass(), $step1->getMethod(),
            $step1->getClass(), $step1->getMethod(),
            $step2->getClass(), $step2->getMethod()
        );

        parent::__construct($message);
    }
}
