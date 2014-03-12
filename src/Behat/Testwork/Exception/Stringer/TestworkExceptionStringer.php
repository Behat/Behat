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
    /**
     * {@inheritdoc}
     */
    public function supportsException(Exception $exception)
    {
        return $exception instanceof TestworkException || $exception instanceof CallErrorException;
    }

    /**
     * {@inheritdoc}
     */
    public function stringException(Exception $exception, $verbosity)
    {
        return trim($exception->getMessage());
    }
}
