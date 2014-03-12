<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Definition\Exception;

use Behat\Behat\Definition\Definition;
use RuntimeException;

/**
 * Represents an exception caused by a redundant step definition.
 *
 * If multiple step definitions in the boundaries of the same suite use same regular expression, behat is not able
 * to determine which one is better and thus this exception is thrown and test suite is stopped.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class RedundantStepException extends RuntimeException implements SearchException
{
    /**
     * Initializes redundant exception.
     *
     * @param Definition $step2 duplicate step definition
     * @param Definition $step1 firstly matched step definition
     */
    public function __construct(Definition $step2, Definition $step1)
    {
        $message = sprintf(
            "Step \"%s\" is already defined in %s\n\n%s\n%s",
            $step2->getPattern(), $step1->getPath(), $step1->getPath(), $step2->getPath()
        );

        parent::__construct($message);
    }
}
