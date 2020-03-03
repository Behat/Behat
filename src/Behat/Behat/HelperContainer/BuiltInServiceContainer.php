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
final class BuiltInServiceContainer implements ContainerInterface
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
        return array_key_exists($id, $this->schema);
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

        if ($factoryMethod = $this->getAndValidateFactoryMethod($reflection, $schema)) {
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

        if (null === $schema) {
            $schema = array('class' => $id);
        }

        if (is_string($schema)) {
            $schema = array('class' => $schema);
        }

        $schema['class'] = $this->getAndValidateClass($id, $schema);
        $schema['arguments'] = $this->getAndValidateArguments($schema);

        return $schema;
    }

    /**
     * Gets and validates a class from schema.
     *
     * @param string       $id
     * @param string|array $schema
     *
     * @return string
     */
    private function getAndValidateClass($id, array $schema)
    {
        if (!isset($schema['class'])) {
            $schema['class'] = $id;
        }

        return $schema['class'];
    }

    /**
     * Gets and validates arguments from schema.
     *
     * @param array $schema
     *
     * @return array
     */
    private function getAndValidateArguments(array $schema)
    {
        return isset($schema['arguments']) ? (array)$schema['arguments'] : array();
    }

    /**
     * Gets and validates a factory method.
     *
     * @param ReflectionClass $reflection
     * @param array           $schema
     *
     * @return null|ReflectionMethod
     */
    private function getAndValidateFactoryMethod(ReflectionClass $reflection, array $schema)
    {
        if (!isset($schema['factory_method'])) {
            return null;
        }

        $factoryMethod = $schema['factory_method'];
        $this->assertFactoryMethodExists($reflection, $factoryMethod);
        $method = $reflection->getMethod($factoryMethod);
        $this->assertFactoryMethodIsStatic($method);

        return $method;
    }

    /**
     * Checks if factory method exists.
     *
     * @param ReflectionClass $class
     * @param string          $methodName
     *
     * @throws WrongServicesConfigurationException
     */
    private function assertFactoryMethodExists(ReflectionClass $class, $methodName)
    {
        if (!$class->hasMethod($methodName)) {
            throw new WrongServicesConfigurationException(sprintf(
                'Factory method `%s::%s` does not exist.',
                $class->getName(),
                $methodName
            ));
        }
    }

    /**
     * Checks if factory method is static.
     *
     * @param ReflectionMethod $method
     *
     * @throws WrongServicesConfigurationException
     */
    private function assertFactoryMethodIsStatic(ReflectionMethod $method)
    {
        if (!$method->isStatic()) {
            throw new WrongServicesConfigurationException(sprintf(
                'Service factory methods must be static, but `%s::%s` is not.',
                $method->getDeclaringClass()->getName(),
                $method->getName()
            ));
        }
    }
}
