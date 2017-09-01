<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\HelperContainer\Call\Filter;

use Behat\Behat\Context\Environment\ServiceContainerEnvironment;
use Behat\Behat\Definition\Call\DefinitionCall;
use Behat\Behat\HelperContainer\Exception\UnsupportedCallException;
use Behat\Behat\Transformation\Call\TransformationCall;
use Behat\Testwork\Call\Call;
use Behat\Testwork\Call\Filter\CallFilter;
use Behat\Testwork\Environment\Call\EnvironmentCall;
use Psr\Container\ContainerInterface;
use ReflectionFunctionAbstract;

/**
 * Dynamically resolves call arguments using the service container.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class ServicesResolver implements CallFilter
{
    /**
     * {@inheritdoc}
     */
    public function supportsCall(Call $call)
    {
        return ($call instanceof DefinitionCall || $call instanceof TransformationCall)
            && $call->getEnvironment() instanceof ServiceContainerEnvironment;
    }

    /**
     * Filters a call and returns a new one.
     *
     * @param Call $call
     *
     * @return Call
     */
    public function filterCall(Call $call)
    {
        if ($container = $this->getContainer($call)) {
            $newArguments = $this->autowireArguments(
                $container,
                $call->getCallee()->getReflection(),
                $call->getArguments()
            );

            return $this->repackageCallWithNewArguments($call, $newArguments);
        }

        return $call;
    }

    /**
     * Gets container from the call.
     *
     * @param Call $call
     *
     * @return null|ContainerInterface
     */
    private function getContainer(Call $call)
    {
        if (!$call instanceof EnvironmentCall) {
            throw new UnsupportedCallException(sprintf(
                'ServicesResolver can not filter `%s` call.',
                get_class($call)
            ), $call);
        }

        if (!$call->getEnvironment() instanceof ServiceContainerEnvironment) {
            throw new UnsupportedCallException(sprintf(
                'ServicesResolver can not filter `%s` call.',
                get_class($call)
            ), $call);
        }

        return $call->getEnvironment()->getServiceContainer();
    }

    /**
     * * Autowires given arguments using provided container.
     *
     * @param ContainerInterface         $container
     * @param ReflectionFunctionAbstract $reflection
     * @param array                      $arguments
     *
     * @return array
     */
    private function autowireArguments(
        ContainerInterface $container,
        ReflectionFunctionAbstract $reflection,
        array $arguments
    ) {
        $newArguments = $arguments;
        foreach ($reflection->getParameters() as $index => $parameter) {
            if (isset($newArguments[$index]) || isset($newArguments[$parameter->getName()])) {
                continue;
            }

            if ($parameter->getClass()) {
                $newArguments[$index] = $container->get($parameter->getClass()->getName());
            }
        }
        return $newArguments;
    }

    /**
     * Repackages old calls with new arguments.
     *
     * @param DefinitionCall|TransformationCall $call
     * @param array                             $arguments
     *
     * @return DefinitionCall|TransformationCall
     */
    private function repackageCallWithNewArguments($call, array $arguments)
    {
        if ($call instanceof DefinitionCall) {
            return new DefinitionCall(
                $call->getEnvironment(),
                $call->getFeature(),
                $call->getStep(),
                $call->getCallee(),
                $arguments,
                $call->getErrorReportingLevel()
            );
        }

        return new TransformationCall(
            $call->getEnvironment(),
            $call->getDefinition(),
            $call->getCallee(),
            $arguments
        );
    }
}
