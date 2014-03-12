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
 * Testwork successful teardown.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class SuccessfulTeardown implements Teardown
{
    /**
     * Returns true if fixtures have been teared down successfully.
     *
     * @return Boolean
     */
    public function isSuccessful()
    {
        return true;
    }

    /**
     * Checks if tear down has produced any output.
     *
     * @return Boolean
     */
    public function hasOutput()
    {
        return false;
    }
}
