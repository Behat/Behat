<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Context\Reader;

use Behat\Behat\Context\Annotation\AnnotationReader;
use Behat\Behat\Context\Environment\ContextEnvironment;
use Behat\Testwork\Call\Callee;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

/**
 * Annotated context reader.
 *
 * Reads context callees by annotations using registered annotation readers.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class AnnotatedContextReader implements ContextReader
{
    /**
     * @var string[]
     */
    private static $ignoreAnnotations = array(
        '@param',
        '@return',
        '@throws',
        '@see',
        '@uses',
        '@todo'
    );
    /**
     * @var AnnotationReader[]
     */
    private $readers = array();

    /**
     * Registers annotation reader.
     *
     * @param AnnotationReader $reader
     */
    public function registerAnnotationReader(AnnotationReader $reader)
    {
        $this->readers[] = $reader;
    }

    /**
     * Loads annotation-based callees from context.
     *
     * @param ContextEnvironment $environment
     * @param string             $contextClassname
     *
     * @return Callee[]
     */
    public function readContextCallees(ContextEnvironment $environment, $contextClassname)
    {
        $reflection = new ReflectionClass($contextClassname);

        $callees = array();
        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            foreach ($this->readMethodCallees($reflection->getName(), $method) as $callee) {
                $callees[] = $callee;
            }
        }

        return $callees;
    }

    /**
     * Loads callees associated with specific method.
     *
     * @param string           $class
     * @param ReflectionMethod $method
     *
     * @return Callee[]
     */
    private function readMethodCallees($class, ReflectionMethod $method)
    {
        $callees = array();

        // read parent annotations
        try {
            $prototype = $method->getPrototype();
            // error occurs on every second PHP stable release - getPrototype() returns itself
            if ($prototype->getDeclaringClass()->getName() !== $method->getDeclaringClass()->getName()) {
                $callees = array_merge($callees, $this->readMethodCallees($class, $prototype));
            }
        } catch (ReflectionException $e) {
        }

        if ($docBlock = $method->getDocComment()) {
            $callees = array_merge($callees, $this->readDocBlockCallees($class, $method, $docBlock));
        }

        return $callees;
    }

    /**
     * Reads callees from the method doc block.
     *
     * @param string           $class
     * @param ReflectionMethod $method
     * @param string           $docBlock
     *
     * @return Callee[]
     */
    private function readDocBlockCallees($class, ReflectionMethod $method, $docBlock)
    {
        $description = null;

        $callees = array();
        foreach (explode("\n", $docBlock) as $docLine) {
            $docLine = preg_replace('/^\/\*\*\s*|^\s*\*\s*|\s*\*\/$|\s*$/', '', $docLine);

            if ('' == $docLine) {
                continue;
            }
            if ('' !== $docLine && '@' !== substr($docLine, 0, 1)) {
                $description = $docLine;

                continue;
            }

            $lowDocLine = strtolower($docLine);
            foreach (self::$ignoreAnnotations as $ignoredAnnotation) {
                if ($ignoredAnnotation == substr($lowDocLine, 0, strlen($ignoredAnnotation))) {
                    continue 2;
                }
            }

            foreach ($this->readers as $reader) {
                if ($callee = $reader->readCallee($class, $method, $docLine, $description)) {
                    $callees[] = $callee;

                    break;
                }
            }
        }

        return $callees;
    }
}
