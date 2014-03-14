<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Tester\Exception\Stringer;

use Behat\Behat\Tester\Exception\PendingException;
use Behat\Testwork\Exception\Stringer\ExceptionStringer;
use Exception;

/**
 * Strings pending exceptions.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class PendingExceptionStringer implements ExceptionStringer
{
    /**
     * {@inheritdoc}
     */
    public function supportsException(Exception $exception)
    {
        return $exception instanceof PendingException;
    }

    /**
     * {@inheritdoc}
     */
    public function stringException(Exception $exception, $verbosity)
    {
        return trim($exception->getMessage());
    }
}
