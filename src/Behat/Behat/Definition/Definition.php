<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Definition;

use Behat\Testwork\Call\Callee;

/**
 * Represents a step definition.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface Definition extends Callee
{
    /**
     * Returns definition type (Given|When|Then).
     *
     * @return string
     */
    public function getType();

    /**
     * Returns step pattern exactly as it was defined.
     *
     * @return string
     */
    public function getPattern();

    /**
     * Represents definition as a string.
     *
     * @return string
     */
    public function __toString();
}
