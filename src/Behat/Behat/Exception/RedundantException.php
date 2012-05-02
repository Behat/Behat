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
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class RedundantException extends BehaviorException
{
    /**
     * Initializes redundant exception.
     *
     * @param DefinitionInterface $step2 duplicate step definition
     * @param DefinitionInterface $step1 firstly matched step definition
     */
    public function __construct(DefinitionInterface $step2, DefinitionInterface $step1)
    {
        $message = sprintf("Step \"%s\" is already defined in %s\n\n%s\n%s",
            $step2->getRegex(), $step1->getPath(), $step1->getPath(), $step2->getPath()
        );

        parent::__construct($message);
    }
}
