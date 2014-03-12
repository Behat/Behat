<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Tester\Result;

use Exception;

/**
 * Represents a result, that possibly produced an exception.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface ExceptionResult extends TestResult
{
    /**
     * Checks that the test result has exception.
     *
     * @return Boolean
     */
    public function hasException();

    /**
     * Returns exception that test result has.
     *
     * @return null|Exception
     */
    public function getException();
}
