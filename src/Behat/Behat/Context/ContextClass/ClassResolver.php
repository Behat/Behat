<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Context\ContextClass;

/**
 * Context class resolver.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface ClassResolver
{
    /**
     * Checks if resolvers supports provided class.
     *
     * @param string $contextClass
     *
     * @return Boolean
     */
    public function supportsClass($contextClass);

    /**
     * Resolves context class.
     *
     * @param string $contextClass
     *
     * @return string
     */
    public function resolveClass($contextClass);
}
