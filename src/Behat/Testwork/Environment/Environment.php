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
 * Represents Testwork test environment.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface Environment
{
    /**
     * Returns environment suite.
     */
    public function getSuite(): Suite;

    /**
     * Creates callable using provided Callee.
     */
    public function bindCallee(Callee $callee): callable;
}
