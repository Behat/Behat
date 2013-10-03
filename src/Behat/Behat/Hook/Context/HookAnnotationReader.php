<?php

namespace Behat\Behat\Hook\Context;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Behat\Behat\Callee\CalleeInterface;
use Behat\Behat\Context\Loader\Annotation\AnnotationReaderInterface;
use ReflectionMethod;

/**
 * Hook annotation reader.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class HookAnnotationReader implements AnnotationReaderInterface
{
    /**
     * @var string
     */
    private static $regex = '/^\@(beforesuite|aftersuite)$/i';
    /**
     * @var string
     */
    private static $filterableRegex = '/^\@(beforefeature|afterfeature|beforescenario|afterscenario|beforestep|afterstep)(?:\s+(.+))?$/i';
    /**
     * @var string[]
     */
    private static $classes = array(
        'beforesuite' => 'Behat\Behat\Hook\Callee\BeforeSuite',
        'aftersuite'  => 'Behat\Behat\Hook\Callee\AfterSuite',
    );
    /**
     * @var string[]
     */
    private static $filterableClasses = array(
        'beforefeature'  => 'Behat\Behat\Hook\Callee\BeforeFeature',
        'afterfeature'   => 'Behat\Behat\Hook\Callee\AfterFeature',
        'beforescenario' => 'Behat\Behat\Hook\Callee\BeforeScenario',
        'afterscenario'  => 'Behat\Behat\Hook\Callee\AfterScenario',
        'beforestep'     => 'Behat\Behat\Hook\Callee\BeforeStep',
        'afterstep'      => 'Behat\Behat\Hook\Callee\AfterStep'
    );

    /**
     * Loads step callees (if exist) associated with specific method.
     *
     * @param ReflectionMethod $method
     * @param string           $docLine
     * @param string           $description
     *
     * @return null|CalleeInterface
     */
    public function readAnnotation(ReflectionMethod $method, $docLine, $description)
    {
        if (preg_match(self::$regex, $docLine, $match)) {
            $type = strtolower($match[1]);
            $class = self::$classes[$type];
            $callable = array($method->getDeclaringClass()->getName(), $method->getName());

            return new $class($callable, $description);
        }

        if (preg_match(self::$filterableRegex, $docLine, $match)) {
            $type = strtolower($match[1]);
            $class = self::$filterableClasses[$type];
            $pattern = isset($match[2]) ? $match[2] : null;
            $callable = array($method->getDeclaringClass()->getName(), $method->getName());

            return new $class($pattern, $callable, $description);
        }

        return null;
    }
}
