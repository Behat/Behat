<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Context\ContextClass;

use Behat\Behat\Context\Environment\Handler\ContextEnvironmentHandler;

/**
 * Resolves arbitrary context strings into a context classes.
 *
 * @see ContextEnvironmentHandler
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * @api
 */
interface ClassResolver
{
    /**
     * Checks if resolvers supports provided class.
     */
    public function supportsClass(string $contextString): bool;

    /**
     * Resolves context class.
     */
    public function resolveClass(string $contextClass): string;
}
