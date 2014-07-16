<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Argument\Exception;

use BadMethodCallException;

/**
 * Represents an exception caused by an unknown function parameter value.
 *
 * Exception is thrown if provided function parameter value is unknown or missing.
 *
 * @author Wouter J <wouter@wouterj.nl>
 */
final class UnknownParameterValueException extends BadMethodCallException implements ArgumentException
{
}
