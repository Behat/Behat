<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Argument;

use Behat\Testwork\Argument\Exception\UnknownParameterValueException;
use Behat\Testwork\Argument\Exception\UnsupportedFunctionException;
use ReflectionFunctionAbstract;
use ReflectionMethod;

/**
 * Organises constructor arguments.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class ConstructorArgumentOrganiser implements ArgumentOrganiser
{
    /**
     * @var ArgumentOrganiser
     */
    private $baseOrganiser;

    /**
     * Initializes organiser.
     *
     * @param ArgumentOrganiser $organiser
     */
    public function __construct(ArgumentOrganiser $organiser)
    {
        $this->baseOrganiser = $organiser;
    }

    /**
     * {@inheritdoc}
     */
    public function organiseArguments(ReflectionFunctionAbstract $function, array $arguments)
    {
        if (!$function instanceof ReflectionMethod) {
            throw new UnsupportedFunctionException(sprintf(
                'ConstructorArgumentOrganiser can only work with ReflectionMethod, but `%s` given.',
                get_class($function)
            ));
        }

        $organisedArguments = $this->baseOrganiser->organiseArguments(
            $function,
            $arguments
        );

        $this->validateArguments($function, $arguments, $organisedArguments);

        return $organisedArguments;
    }

    /**
     * Checks that all provided constructor arguments are present in the constructor.
     *
     * @param ReflectionMethod $constructor
     * @param mixed[]          $passedArguments
     * @param mixed[]          $organisedArguments
     *
     * @throws UnknownParameterValueException
     */
    private function validateArguments(
        ReflectionMethod $constructor,
        array $passedArguments,
        array $organisedArguments
    ) {
        foreach ($passedArguments as $key => $val) {
            if (array_key_exists($key, $organisedArguments)) {
                continue;
            }

            throw new UnknownParameterValueException(
                sprintf(
                    '`%s::__construct()` does not expect argument `$%s`.',
                    $constructor->getDeclaringClass()->getName(),
                    $key
                )
            );
        }
    }
}
