<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\HelperContainer\Argument;

use Behat\Behat\Context\Argument\SuiteScopedResolverFactory;
use Behat\Behat\HelperContainer\BuiltInServiceContainer;
use Behat\Behat\HelperContainer\Exception\WrongContainerClassException;
use Behat\Behat\HelperContainer\Exception\WrongServicesConfigurationException;
use Behat\Behat\HelperContainer\ServiceContainer\HelperContainerExtension;
use Behat\Testwork\Suite\Suite;
use Interop\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\TaggedContainerInterface;

/**
 * Generates ServiceContainer argument resolvers based on suite's `services` setting.
 *
 * @see ContextEnvironmentHandler
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class ServicesResolverFactory implements SuiteScopedResolverFactory
{
    /**
     * @var TaggedContainerInterface
     */
    private $container;

    /**
     * Initialises factory.
     *
     * @param TaggedContainerInterface $container
     */
    public function __construct(TaggedContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function generateArgumentResolvers(Suite $suite)
    {
        if (!$suite->hasSetting('services')) {
            return array();
        }

        $container = $this->createContainer($suite->getSetting('services'));

        return array($this->createArgumentResolver($container));
    }

    /**
     * Creates container from the setting passed.
     *
     * @param string $settings
     *
     * @return mixed
     */
    private function createContainer($settings)
    {
        if (is_string($settings)) {
            return $this->createContainerFromString($settings);
        }

        if (is_array($settings)) {
            return $this->createContainerFromArray($settings);
        }

        throw new WrongServicesConfigurationException(
            sprintf('`services` must be either string or an array, but `%s` given.', gettype($settings))
        );
    }

    /**
     * Creates custom container using class/constructor given.
     *
     * @param string $settings
     *
     * @return mixed
     */
    private function createContainerFromString($settings)
    {
        if ('@' === mb_substr($settings, 0, 1)) {
            return $this->loadContainerFromContainer(mb_substr($settings, 1));
        }

        return $this->createContainerFromClassSpec($settings);
    }

    /**
     * Creates built-in service container with provided settings.
     *
     * @param array $settings
     *
     * @return BuiltInServiceContainer
     */
    private function createContainerFromArray(array $settings)
    {
        return new BuiltInServiceContainer($settings);
    }

    /**
     * Loads container from string.
     *
     * @param string $name
     *
     * @return mixed
     */
    private function loadContainerFromContainer($name)
    {
        $services = $this->container->findTaggedServiceIds(HelperContainerExtension::HELPER_CONTAINER_TAG);

        if (!in_array($name, array_keys($services))) {
            throw new WrongServicesConfigurationException(
                sprintf('Service container `@%s` was not found.', $name)
            );
        }

        return $this->container->get($name);
    }

    /**
     * Creates container from string-based class spec.
     *
     * @param string $classSpec
     *
     * @return mixed
     */
    private function createContainerFromClassSpec($classSpec)
    {
        $constructor = explode('::', $classSpec);

        if (2 == count($constructor)) {
            return call_user_func($constructor);
        }

        return new $constructor[0];
    }

    /**
     * Checks if container implements the correct interface and creates resolver using it.
     *
     * @param mixed $container
     *
     * @return ServicesResolver
     */
    private function createArgumentResolver($container)
    {
        if (!$container instanceof ContainerInterface) {
            throw new WrongContainerClassException(
                sprintf(
                    'Service container is expected to implement `Interop\Container\ContainerInterface`, but `%s` does not.',
                    get_class($container)
                ),
                get_class($container)
            );
        }

        return new ServicesResolver($container);
    }
}
