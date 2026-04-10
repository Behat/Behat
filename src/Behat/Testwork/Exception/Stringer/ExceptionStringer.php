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
 *
 * @api
 */
interface ExceptionStringer
{
    /**
     * Checks if stringer supports provided exception.
     */
    public function supportsException(Exception $exception): bool;

    /**
     * Strings provided exception.
     */
    public function stringException(Exception $exception, int $verbosity): string;
}
