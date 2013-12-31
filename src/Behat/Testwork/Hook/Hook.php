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
 * Testwork hook interface.
 *
 * All testwork test hooks should implement this interface.
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
     * Returns hooked event name.
     *
     * @return string
     */
    public function getHookedEventName();

    /**
     * Represents hook as a string.
     *
     * @return string
     */
    public function __toString();
}
