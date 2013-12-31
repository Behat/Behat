<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Environment;

use Behat\Testwork\Call\Callee;

/**
 * Testwork test environment interface.
 *
 * All testwork test environments should implement this interface.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface Environment
{
    /**
     * Returns environment suite name.
     *
     * @return string
     */
    public function getSuiteName();

    /**
     * Creates callable using provided Callee.
     *
     * @param Callee $callee
     *
     * @return callable
     */
    public function bindCallee(Callee $callee);
}
