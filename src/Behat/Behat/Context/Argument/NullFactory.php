<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Context\Argument;

use Behat\Testwork\Suite\Suite;

/**
 * NoOp factory. Always returns zero resolvers.
 *
 * @see ContextEnvironmentHandler
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class NullFactory implements SuiteScopedResolverFactory
{
    /**
     * {@inheritdoc}
     */
    public function generateArgumentResolvers(Suite $suite)
    {
        return array();
    }
}
