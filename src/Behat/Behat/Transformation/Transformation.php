<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Transformation;

use Behat\Testwork\Call\Callee;

/**
 * Step transformation interface.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface Transformation extends Callee
{
    /**
     * Returns transformation pattern exactly as it was defined.
     *
     * @return string
     */
    public function getPattern();

    /**
     * Represents transformation as a string.
     *
     * @return string
     */
    public function __toString();
}
