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
        if (!$call instanceof EnvironmentCall || !$call->getEnvironment() instanceof ServiceContainerEnvironment) {
            throw new UnsupportedCallException(sprintf(
                'ServicesResolver can not filter `%s` call.',
                get_class($call)
            ), $call);
        }

        $container = $call->getEnvironment()->getServiceContainer();
        $newArguments = $call->getArguments();

        if ($container) {
            foreach ($call->getCallee()->getReflection()->getParameters() as $index => $parameter) {
                if (isset($newArguments[$index]) || isset($newArguments[$parameter->getName()])) {
                    continue;
                }

                if ($parameter->getClass() && $container->has($parameter->getClass()->getName())) {
                    $newArguments[$index] = $container->get($parameter->getClass()->getName());
                }
            }
        }

        if ($call instanceof DefinitionCall) {
            return new DefinitionCall(
                $call->getEnvironment(),
                $call->getFeature(),
                $call->getStep(),
                $call->getCallee(),
                $newArguments,
                $call->getErrorReportingLevel()
            );
        }

        if ($call instanceof TransformationCall) {
            return new TransformationCall(
                $call->getEnvironment(),
                $call->getDefinition(),
                $call->getCallee(),
                $newArguments
            );
        }

        return $call;
    }
}
