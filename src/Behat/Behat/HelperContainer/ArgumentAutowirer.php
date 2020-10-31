<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\HelperContainer;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface as PsrContainerInterface;
use ReflectionClass;
use ReflectionFunctionAbstract;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionException;

/**
 * Automatically wires arguments of a given function from inside the container by using type-hints.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class ArgumentAutowirer
{
    /**
     * @var PsrContainerInterface
     */
    private $container;

    /**
     * Initialises wirer.
     *
     * @param PsrContainerInterface $container
     */
    public function __construct(PsrContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Autowires given arguments using provided container.
     *
     * @param ReflectionFunctionAbstract $reflection
     * @param array $arguments
     *
     * @return array
     *
     * @throws ContainerExceptionInterface if unset argument typehint can not be resolved from container
     */
    public function autowireArguments(ReflectionFunctionAbstract $reflection, array $arguments)
    {
        $newArguments = $arguments;
        foreach ($reflection->getParameters() as $index => $parameter) {
            if ($this->isArgumentWireable($newArguments, $index, $parameter)) {
                $newArguments[$index] = $this->container->get($this->getClassFromParameter($parameter));
            }
        }

        return $newArguments;
    }

    /**
     * Checks if given argument is wireable.
     *
     * Argument is wireable if it was not previously set and it has a class type-hint.
     *
     * @param array               $arguments
     * @param integer             $index
     * @param ReflectionParameter $parameter
     *
     * @return bool
     */
    private function isArgumentWireable(array $arguments, $index, ReflectionParameter $parameter)
    {
        if (isset($arguments[$index]) || array_key_exists($index, $arguments)) {
            return false;
        }

        if (isset($arguments[$parameter->getName()]) || array_key_exists($parameter->getName(), $arguments)) {
            return false;
        }

        return (bool) $this->getClassFromParameter($parameter);
    }

    private function getClassFromParameter(ReflectionParameter $parameter) : ?string
    {
        if (!($type = $parameter->getType()) || !($type instanceof ReflectionNamedType)) {
            return null;
        }

        try {
            $typeString = $type->getName();

            if ($typeString == 'self') {
                return $parameter->getDeclaringClass()->getName();
            }
            elseif ($typeString == 'parent') {
                return $parameter->getDeclaringClass()->getParentClass()->getName();
            }

            // will throw if not valid class
            new ReflectionClass($typeString);

            return $typeString;

        } catch (ReflectionException $e) {
            return null;
        }
    }
}
