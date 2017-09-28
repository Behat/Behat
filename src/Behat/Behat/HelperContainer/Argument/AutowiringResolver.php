<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\HelperContainer\Argument;

use Behat\Behat\Context\Argument\ArgumentResolver;
use Behat\Behat\HelperContainer\ArgumentAutowirer;
use Psr\Container\ContainerInterface;
use ReflectionClass;

/**
 * Resolves arguments that weren't resolved before by autowiring.
 *
 * @see ContextFactory
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class AutowiringResolver implements ArgumentResolver
{
    /**
     * @var ArgumentAutowirer
     */
    private $autowirer;

    /**
     * Initialises resolver.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->autowirer = new ArgumentAutowirer($container);
    }

    /**
     * {@inheritdoc}
     */
    public function resolveArguments(ReflectionClass $classReflection, array $arguments)
    {
        if ($constructor = $classReflection->getConstructor()) {
            return $this->autowirer->autowireArguments($constructor, $arguments);
        }

        return $arguments;
    }
}
