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
 *
 * @api
 */
interface Transformation extends Callee
{
    /**
     * Represents transformation as a string.
     *
     * @return string
     */
    public function __toString();
}
