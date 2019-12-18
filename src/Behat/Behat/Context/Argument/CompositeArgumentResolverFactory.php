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
 * Composite factory. Delegates to other (registered) factories to do the job.
 *
 * @see ContextEnvironmentHandler
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class CompositeArgumentResolverFactory implements ArgumentResolverFactory
{
    /**
     * @var ArgumentResolverFactory[]
     */
    private $factories = array();

    /**
     * Registers factory.
     *
     * @param ArgumentResolverFactory $factory
     */
    public function registerFactory(ArgumentResolverFactory $factory)
    {
        $this->factories[] = $factory;
    }

    /**
     * {@inheritdoc}
     */
    public function createArgumentResolvers(Environment $environment)
    {
        return array_reduce(
            $this->factories,
            function (array $resolvers, ArgumentResolverFactory $factory) use ($environment) {
                return array_merge($resolvers, $factory->createArgumentResolvers($environment));
            },
            array()
        );
    }
}
