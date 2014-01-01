<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Exception\Stringer;

use Exception;
use PHPUnit_Framework_Exception;
use PHPUnit_Framework_TestFailure;

/**
 * PHPUnit exception stringer.
 *
 * Strings PHPUnit assertion exceptions.
 *
 * @see ExceptionPresenter
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class PHPUnitExceptionStringer implements ExceptionStringer
{
    /**
     * Checks if stringer supports provided exception.
     *
     * @param Exception $exception
     *
     * @return Boolean
     */
    public function supportsException(Exception $exception)
    {
        return $exception instanceof PHPUnit_Framework_Exception;
    }

    /**
     * Strings provided exception.
     *
     * @param Exception $exception
     * @param integer   $verbosity
     *
     * @return string
     */
    public function stringException(Exception $exception, $verbosity)
    {
        // PHPUnit assertion exceptions do not include expected / observed info in their
        // messages, but expect the test listeners to format that info like the following
        // (see e.g. PHPUnit_TextUI_ResultPrinter::printDefectTrace)

        return trim(PHPUnit_Framework_TestFailure::exceptionToString($exception));
    }
}
