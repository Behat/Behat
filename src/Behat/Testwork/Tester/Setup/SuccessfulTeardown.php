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
 * Represents successful teardown.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class SuccessfulTeardown implements Teardown
{
    /**
     * {@inheritdoc}
     */
    public function isSuccessful()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function hasOutput()
    {
        return false;
    }
}
