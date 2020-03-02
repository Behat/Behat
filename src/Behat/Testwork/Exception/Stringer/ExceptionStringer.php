<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Exception\Stringer;

use Behat\Testwork\Exception\ExceptionPresenter;
use Exception;

/**
 * Finds a best way to present as a string particular.
 *
 * @see ExceptionPresenter
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface ExceptionStringer
{
    /**
     * Checks if stringer supports provided exception.
     *
     * @param Exception $exception
     *
     * @return bool
     */
    public function supportsException(Exception $exception);

    /**
     * Strings provided exception.
     *
     * @param Exception $exception
     * @param integer   $verbosity
     *
     * @return string
     */
    public function stringException(Exception $exception, $verbosity);
}
