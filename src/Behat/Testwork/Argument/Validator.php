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
            $name = $parameter->getName();

            if ($parameter->isDefaultValueAvailable()
                || array_key_exists($num, $arguments)
                || array_key_exists($name, $arguments)) {
                continue;
            }

            throw new UnknownParameterValueException(sprintf(
                'Can not find a matching value for an argument `$%s` of the method `%s`.',
                $name,
                $this->getFunctionPath($function)
            ));
        }
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
