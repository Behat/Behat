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
use Behat\Testwork\Suite\Suite;

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
     * Returns environment suite.
     *
     * @return Suite
     */
    public function getSuite();

    /**
     * Creates callable using provided Callee.
     *
     * @param Callee $callee
     *
     * @return callable
     */
    public function bindCallee(Callee $callee);
}
