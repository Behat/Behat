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
 * Adapts SuiteScopedResolverFactory to new ArgumentResolverFactory interface.
 *
 * @see ContextEnvironmentHandler
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * @deprecated since 3.4. Use `ArgumentResolverFactory` instead
 */
final class SuiteScopedResolverFactoryAdapter implements ArgumentResolverFactory
{
    /**
     * @var SuiteScopedResolverFactory
     */
    private $factory;

    /**
     * Initialises adapter.
     *
     * @param SuiteScopedResolverFactory $factory
     */
    public function __construct(SuiteScopedResolverFactory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * {@inheritdoc}
     */
    public function createArgumentResolvers(Environment $environment)
    {
        return $this->factory->generateArgumentResolvers($environment->getSuite());
    }
}
