<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Exception\Stringer;

use Behat\Testwork\Call\Exception\CallErrorException;
use Behat\Testwork\Exception\TestworkException;
use Exception;

/**
 * Strings Testwork exceptions.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class TestworkExceptionStringer implements ExceptionStringer
{
    public function supportsException(Exception $exception): bool
    {
        return $exception instanceof TestworkException || $exception instanceof CallErrorException;
    }

    public function stringException(Exception $exception, $verbosity): string
    {
        return trim($exception->getMessage());
    }
}
