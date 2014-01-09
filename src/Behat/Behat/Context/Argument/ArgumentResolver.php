<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Context\Argument;

use Behat\Behat\Context\Environment\Handler\ContextEnvironmentHandler;

/**
 * Context constructor argument resolver.
 *
 * Used by ContextEnvironmentHandler to resolve specific context arguments.
 *
 * @see ContextEnvironmentHandler
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface ArgumentResolver
{
    /**
     * Resolves context constructor arguments.
     *
     * @param string  $class
     * @param mixed[] $arguments
     *
     * @return mixed[]
     */
    public function resolveArguments($class, array $arguments);
}
