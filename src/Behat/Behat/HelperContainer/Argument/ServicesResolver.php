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
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use ReflectionClass;

/**
 * Resolves arguments using provided service container.
 *
 * @see ContextFactory
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class ServicesResolver implements ArgumentResolver
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * Initialises resolver.
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @throws ContainerExceptionInterface
     */
    public function resolveArguments(ReflectionClass $classReflection, array $arguments)
    {
        return array_map([$this, 'resolveArgument'], $arguments);
    }

    /**
     * Attempts to resolve singular argument from container.
     *
     * Convention is strings starting with `@` are considered services and
     * are expected to be present in the container.
     *
     * @throws ContainerExceptionInterface
     */
    private function resolveArgument($value)
    {
        if (is_string($value) && 0 === mb_strpos($value, '@')) {
            return $this->container->get(mb_substr($value, 1));
        }

        return $value;
    }
}
