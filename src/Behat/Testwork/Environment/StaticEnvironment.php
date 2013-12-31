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
 * Static calls environment.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class StaticEnvironment implements Environment
{
    /**
     * @var string
     */
    private $suiteName;

    /**
     * Initializes environment.
     *
     * @param string $suiteName
     */
    public function __construct($suiteName)
    {
        $this->suiteName = $suiteName;
    }

    /**
     * Returns environment suite name.
     *
     * @return string
     */
    public function getSuiteName()
    {
        return $this->suiteName;
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
