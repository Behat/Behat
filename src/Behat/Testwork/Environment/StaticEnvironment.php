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
 * Represents static calls environment.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class StaticEnvironment implements Environment
{
    /**
     * Initializes environment.
     */
    public function __construct(
        private readonly Suite $suite,
    ) {
    }

    final public function getSuite(): Suite
    {
        return $this->suite;
    }

    final public function bindCallee(Callee $callee)
    {
        return $callee->getCallable();
    }
}
