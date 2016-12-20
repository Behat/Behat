<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\HelperContainer;

use Behat\Behat\HelperContainer\Exception\ServiceNotFoundException;
use Behat\Behat\HelperContainer\Exception\WrongServicesConfigurationException;
use ReflectionClass;
use ReflectionMethod;

/**
 * Built-in service container.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class BuiltInServiceContainer implements ServiceContainer
{
    /**
     * @var array
     */
    private $schema;
    /**
     * @var array
     */
    private $instances;

    /**
     * Initialises container using provided service configuration.
     *
     * @param array $schema
     */
    public function __construct(array $schema)
    {
        $this->schema = $schema;
    }

    /**
     * {@inheritdoc}
     */
    public function has($id)
    {
        return isset($this->schema[$id]);
    }

    /**
     * {@inheritdoc}
     */
    public function get($id)
    {
        if (!$this->has($id)) {
            throw new ServiceNotFoundException(
                sprintf('Service with id `%s` was not defined inside the `services` setting`.', $id),
                $id
            );
        }

        return $this->instances[$id] = isset($this->instances[$id]) ? $this->instances[$id] : $this->createInstance($id);
    }

    /**
     * Creates an instance of given service.
     *
     * @param string $id
     *
     * @return mixed
     */
    private function createInstance($id)
    {
        $schema = $this->getAndValidateServiceSchema($id);

        $reflection = new ReflectionClass($schema['class']);
        $arguments = $schema['arguments'];

        if ($factoryMethod = $this->getAndValidateFactoryMethod($reflection, $schema['factory_method'])) {
            return $factoryMethod->invokeArgs(null, $arguments);
        }

        return $reflection->newInstanceArgs($arguments);
    }

    /**
     * Gets and validates a service configuration for a service with given ID.
     *
     * @param string $id
     *
     * @throws WrongServicesConfigurationException
     *
     * @return array|string
     */
    private function getAndValidateServiceSchema($id)
    {
        $schema = $this->schema[$id];

        if (is_string($schema)) {
            $schema = array('class' => $schema);
        }

        if (!isset($schema['class'])) {
            throw new WrongServicesConfigurationException(sprintf(
                'All services of the built-in `services` must have `class` option set, but `%s` does not.',
                $id
            ));
        }

        $schema['arguments'] = isset($schema['arguments']) ? (array)$schema['arguments'] : array();
        $schema['factory_method'] = isset($schema['factory_method']) ? $schema['factory_method'] : null;

        return $schema;
    }

    /**
     * Gets and validates a factory method.
     *
     * @param ReflectionClass $reflection
     * @param null|string     $factoryMethod
     *
     * @throws WrongServicesConfigurationException
     *
     * @return null|ReflectionMethod
     */
    private function getAndValidateFactoryMethod(ReflectionClass $reflection, $factoryMethod)
    {
        if (null === $factoryMethod) {
            return null;
        }

        if (!$reflection->hasMethod($factoryMethod)) {
            throw new WrongServicesConfigurationException(sprintf(
                'Factory method `%s::%s` does not exist.',
                $reflection->getName(),
                $factoryMethod
            ));
        }

        $method = $reflection->getMethod($factoryMethod);

        if (!$method->isStatic()) {
            throw new WrongServicesConfigurationException(sprintf(
                'Service factory methods must be static, but `%s::%s` is not.',
                $reflection->getName(),
                $factoryMethod
            ));
        }

        return $method;
    }
}
