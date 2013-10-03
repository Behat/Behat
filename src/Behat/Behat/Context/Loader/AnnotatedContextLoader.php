<?php

namespace Behat\Behat\Context\Loader;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Behat\Behat\Callee\CalleeInterface;
use Behat\Behat\Context\Loader\Annotation\AnnotationReaderInterface;
use Behat\Behat\Context\Loader\LoaderInterface;
use Behat\Behat\Suite\SuiteInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

/**
 * Annotated context loader.
 * Loads context callees from annotated methods.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class AnnotatedContextLoader implements LoaderInterface
{
    /**
     * @var string[]
     */
    private static $ignoreAnnotations = array('@param', '@return', '@throws', '@see', '@uses', '@todo');
    /**
     * @var AnnotationReaderInterface[]
     */
    private $readers = array();

    /**
     * Registers annotation reader.
     *
     * @param AnnotationReaderInterface $reader
     */
    public function registerAnnotationReader(AnnotationReaderInterface $reader)
    {
        $this->readers[] = $reader;
    }

    /**
     * Loads annotation-based callees from context.
     *
     * @param SuiteInterface $suite
     * @param string         $contextClass
     *
     * @return CalleeInterface[]
     */
    public function loadCallees(SuiteInterface $suite, $contextClass)
    {
        $callees = array();

        $reflection = new ReflectionClass($contextClass);
        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            foreach ($this->loadMethodCallees($reflection->getName(), $method) as $callback) {
                $callees[] = $callback;
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
     * @return CalleeInterface[]
     */
    private function loadMethodCallees($class, ReflectionMethod $method)
    {
        $callees = array();

        // read parent annotations
        try {
            $prototype = $method->getPrototype();
            // error occurs on every second PHP stable release - getPrototype() returns itself
            if ($prototype->getDeclaringClass()->getName() !== $method->getDeclaringClass()->getName()) {
                $callees = array_merge($callees, $this->loadMethodCallees($class, $prototype));
            }
        } catch (ReflectionException $e) {
        }

        if ($docBlock = $method->getDocComment()) {
            $description = null;

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
                    if ($callee = $reader->readAnnotation($method, $docLine, $description)) {
                        $callees[] = $callee;

                        break;
                    }
                }
            }
        }

        return $callees;
    }
}
