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
 * Strings PHPUnit assertion exceptions.
 *
 * @see ExceptionPresenter
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class PHPUnitExceptionStringer implements ExceptionStringer
{
    /**
     * {@inheritdoc}
     */
    public function supportsException(Exception $exception)
    {
        return $exception instanceof PHPUnit_Framework_Exception;
    }

    /**
     * {@inheritdoc}
     */
    public function stringException(Exception $exception, $verbosity)
    {
        // PHPUnit assertion exceptions do not include expected / observed info in their
        // messages, but expect the test listeners to format that info like the following
        // (see e.g. PHPUnit_TextUI_ResultPrinter::printDefectTrace)
        return trim(PHPUnit_Framework_TestFailure::exceptionToString($exception));
    }
}
