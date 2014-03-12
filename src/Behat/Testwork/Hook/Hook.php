<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Hook;

use Behat\Testwork\Call\Callee;

/**
 * Represents a Testwork hook.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface Hook extends Callee
{
    /**
     * Returns hook name.
     *
     * @return string
     */
    public function getName();

    /**
     * Returns hook scope name.
     *
     * @return string
     */
    public function getScopeName();

    /**
     * Represents hook as a string.
     *
     * @return string
     */
    public function __toString();
}
