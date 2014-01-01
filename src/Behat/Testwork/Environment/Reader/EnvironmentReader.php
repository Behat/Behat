<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Environment\Reader;

use Behat\Testwork\Call\Callee;
use Behat\Testwork\Environment\Environment;

/**
 * Testwork test environment reader interface.
 *
 * Reads callees from a provided environment.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface EnvironmentReader
{
    /**
     * Checks if reader supports an environment.
     *
     * @param Environment $environment
     *
     * @return Boolean
     */
    public function supportsEnvironment(Environment $environment);

    /**
     * Reads callees from an environment.
     *
     * @param Environment $environment
     *
     * @return Callee[]
     */
    public function readEnvironmentCallees(Environment $environment);
}
