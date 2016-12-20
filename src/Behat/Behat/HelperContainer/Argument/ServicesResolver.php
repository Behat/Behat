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
use Interop\Container\ContainerInterface;
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
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function resolveArguments(ReflectionClass $classReflection, array $arguments)
    {
        return array_map(array($this, 'resolveArgument'), $arguments);
    }

    /**
     * Attempts to resolve singular argument from container.
     *
     * Convention is strings starting with `@` are considered services and
     * are expected to be present in the container.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    private function resolveArgument($value)
    {
        if ('@' === mb_substr($value, 0, 1)) {
            return $this->container->get(mb_substr($value, 1));
        }

        return $value;
    }
}
