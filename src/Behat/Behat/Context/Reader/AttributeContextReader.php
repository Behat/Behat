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
use ReflectionClass;
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
    private $readers = array();

    /**
     * Registers attribute reader.
     *
     * @param AttributeReader $reader
     */
    public function registerAttributeReader(AttributeReader $reader)
    {
        $this->readers[] = $reader;
    }

    /**
     * {@inheritdoc}
     */
    public function readContextCallees(ContextEnvironment $environment, $contextClass)
    {
        if (\PHP_MAJOR_VERSION < 8) {
            return [];
        }

        $reflection = new ReflectionClass($contextClass);

        $callees = array();
        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            foreach ($this->readMethodCallees($reflection->getName(), $method) as $callee) {
                $callees[] = $callee;
            }
        }

        return $callees;
    }

    private function readMethodCallees(string $contextClass, ReflectionMethod $method)
    {
        $callees = [];
        foreach ($this->readers as $reader) {
            $callees = array_merge($callees, $reader->readCallees($contextClass, $method));
        }

        return $callees;
    }
}
