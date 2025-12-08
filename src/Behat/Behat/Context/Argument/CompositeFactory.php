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
 * Composite factory. Delegates to other (registered) factories to do the job.
 *
 * @see ContextEnvironmentHandler
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * @deprecated and will be removed in 4.0. Use CompositeArgumentResolverFactory instead
 */
final class CompositeFactory implements SuiteScopedResolverFactory
{
    /**
     * @var SuiteScopedResolverFactory[]
     */
    private $factories = [];

    /**
     * Registers factory.
     */
    public function registerFactory(SuiteScopedResolverFactory $factory): void
    {
        $this->factories[] = $factory;
    }

    public function generateArgumentResolvers(Suite $suite)
    {
        return array_reduce(
            $this->factories,
            fn (array $resolvers, SuiteScopedResolverFactory $factory): array => array_merge($resolvers, $factory->generateArgumentResolvers($suite)),
            []
        );
    }
}
