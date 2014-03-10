<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Tester\Setup;

/**
 * Testwork failed setup.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class FailedSetup implements Setup
{
    /**
     * Returns true if fixtures have been handled successfully.
     *
     * @return Boolean
     */
    public function isSuccessful()
    {
        return false;
    }

    /**
     * Checks if setup has produced any output.
     *
     * @return Boolean
     */
    public function hasOutput()
    {
        return false;
    }
}
