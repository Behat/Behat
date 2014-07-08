<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Call\Exception;

use BadMethodCallException;

/**
 * Represents an exception caused by an unknown parameter value.
 *
 * If the value of a parameter is not inside the matches and the parameter does 
 * not have a default value, behat is not able to determine the value of the 
 * parameter and  thus this exception is thrown and test suite is stopped.
 *
 * @author Wouter J <wouter@wouterj.nl>
 */
final class UnknownParameterValueException extends BadMethodCallException implements CallException
{
}
