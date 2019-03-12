<?php

/*
 * This file is part of the Behat.
 * (c) Andrew Nicols <andrew@nicols.co.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Definition\Exception;

use Behat\Behat\Definition\Definition;
use RuntimeException;

/**
 * Represents an exception caused by a BeforeStepException.
 *
 * If multiple step definitions in the boundaries of the same suite use same regular expression, behat is not able
 * to determine which one is better and thus this exception is thrown and test suite is stopped.
 *
 * @author Andrew Nicols <andrew@nicols.co.uk>
 */
final class BeforeStepException extends RuntimeException implements SearchException
{
    /**
     * Initializes BeforeStepException.
     */
    public function __construct()
    {
        $message = sprintf(
            "Step skipped due to failed BeforeStep setup"
        );

        parent::__construct($message);
    }
}
