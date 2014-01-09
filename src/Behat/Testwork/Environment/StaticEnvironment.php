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
 * Static calls environment.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class StaticEnvironment implements Environment
{
    /**
     * @var Suite
     */
    private $suite;

    /**
     * Initializes environment.
     *
     * @param Suite $suite
     */
    public function __construct(Suite $suite)
    {
        $this->suite = $suite;
    }

    /**
     * Returns environment suite.
     *
     * @return Suite
     */
    public function getSuite()
    {
        return $this->suite;
    }

    /**
     * Creates callable using provided Callee.
     *
     * @param Callee $callee
     *
     * @return callable
     */
    public function bindCallee(Callee $callee)
    {
        return $callee->getCallable();
    }
}
