<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Argument;

use Behat\Testwork\Argument\Exception\UnknownParameterValueException;
use ReflectionFunctionAbstract;
use ReflectionMethod;
use ReflectionParameter;

/**
 * Validates function arguments.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Validator
{
    /**
     * Validates that all arguments are in place, throws exception otherwise.
     *
     * @param ReflectionFunctionAbstract $function
     * @param mixed[]                    $arguments
     *
     * @throws UnknownParameterValueException
     */
    public function validateArguments(ReflectionFunctionAbstract $function, array $arguments)
    {
        foreach ($function->getParameters() as $num => $parameter) {
            $this->validateArgument($parameter, $num, $arguments);
        }
    }

    /**
     * Validates given argument.
     *
     * @param ReflectionParameter $parameter
     * @param integer             $parameterIndex
     * @param array               $givenArguments
     */
    private function validateArgument(ReflectionParameter $parameter, $parameterIndex, array $givenArguments)
    {
        if ($parameter->isDefaultValueAvailable()) {
            return;
        }

        if (array_key_exists($parameterIndex, $givenArguments)) {
            return;
        }

        if (array_key_exists($parameter->getName(), $givenArguments)) {
            return;
        }

        throw new UnknownParameterValueException(sprintf(
            'Can not find a matching value for an argument `$%s` of the method `%s`.',
            $parameter->getName(),
            $this->getFunctionPath($parameter->getDeclaringFunction())
        ));
    }

    /**
     * Returns function path for a provided reflection.
     *
     * @param ReflectionFunctionAbstract $function
     *
     * @return string
     */
    private function getFunctionPath(ReflectionFunctionAbstract $function)
    {
        if ($function instanceof ReflectionMethod) {
            return sprintf(
                '%s::%s()',
                $function->getDeclaringClass()->getName(),
                $function->getName()
            );
        }

        return sprintf('%s()', $function->getName());
    }
}
