<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Context\Reader;

use Behat\Behat\Context\Attribute\AttributeReader;
use Behat\Behat\Context\Environment\ContextEnvironment;
use Behat\Testwork\Call\RuntimeCallee;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

/**
 * Reads context callees by Attributes using registered Attribute readers.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class AttributeContextReader implements ContextReader
{
    /**
     * @var AttributeReader[]
     */
    private $readers = [];

    /**
     * Registers attribute reader.
     */
    public function registerAttributeReader(AttributeReader $reader): void
    {
        $this->readers[] = $reader;
    }

    /**
     * @return list<RuntimeCallee>
     */
    public function readContextCallees(ContextEnvironment $environment, $contextClass): array
    {
        $reflection = new ReflectionClass($contextClass);

        $callees = [];

        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            foreach ($this->readMethodCallees($reflection->getName(), $method) as $callee) {
                $callees[] = $callee;
            }
        }

        return $callees;
    }

    /**
     * @return list<RuntimeCallee>
     */
    private function readMethodCallees(string $contextClass, ReflectionMethod $method): array
    {
        $callees = [];

        foreach ($this->readers as $reader) {
            $callees = array_merge(
                $this->readParentCallees($contextClass, $method, $reader, $callees),
                $reader->readCallees($contextClass, $method)
            );
        }

        return $callees;
    }

    /**
     * @return list<RuntimeCallee>
     */
    private function readParentCallees(
        string $contextClass,
        ReflectionMethod $method,
        AttributeReader $reader,
        array $callees,
    ): array {
        try {
            $prototype = $method->getPrototype();

            if ($prototype->getDeclaringClass()->getName() !== $method->getDeclaringClass()->getName()) {
                $callees = array_merge($callees, $reader->readCallees($contextClass, $prototype));
            }
        } catch (ReflectionException) {
        }

        return $callees;
    }
}
