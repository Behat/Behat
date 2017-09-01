<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Context\Argument;

use Behat\Testwork\Environment\Environment;

/**
 * Creates argument resolvers for provided environment.
 *
 * @see ContextEnvironmentHandler
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface ArgumentResolverFactory
{
    /**
     * Builds argument resolvers for provided suite.
     *
     * @param Environment $environment
     *
     * @return ArgumentResolver[]
     */
    public function createArgumentResolvers(Environment $environment);
}
