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
 * Represents a result of test setUp action.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface Setup
{
    /**
     * Returns true if fixtures have been handled successfully.
     *
     * @return bool
     */
    public function isSuccessful();

    /**
     * Checks if setup has produced any output.
     *
     * @return bool
     */
    public function hasOutput();
}
