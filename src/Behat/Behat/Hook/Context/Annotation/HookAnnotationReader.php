<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Hook\Context\Annotation;

use Behat\Behat\Context\Annotation\AnnotationReader;
use Behat\Testwork\Hook\Call\RuntimeHook;
use ReflectionMethod;

/**
 * Reads hook callees from context method annotations.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class HookAnnotationReader implements AnnotationReader
{
    /**
     * @var string
     */
    private static $regex = '/^\@(beforesuite|aftersuite|beforefeature|afterfeature|beforescenario|afterscenario|beforestep|afterstep)(?:\s+(.+))?$/i';
    /**
     * @var string[]
     */
    private static $classes = array(
        'beforesuite'    => 'Behat\Testwork\Hook\Call\BeforeSuite',
        'aftersuite'     => 'Behat\Testwork\Hook\Call\AfterSuite',
        'beforefeature'  => 'Behat\Behat\Hook\Call\BeforeFeature',
        'afterfeature'   => 'Behat\Behat\Hook\Call\AfterFeature',
        'beforescenario' => 'Behat\Behat\Hook\Call\BeforeScenario',
        'afterscenario'  => 'Behat\Behat\Hook\Call\AfterScenario',
        'beforestep'     => 'Behat\Behat\Hook\Call\BeforeStep',
        'afterstep'      => 'Behat\Behat\Hook\Call\AfterStep'
    );

    /**
     * Loads step callees (if exist) associated with specific method.
     *
     * @param string           $contextClass
     * @param ReflectionMethod $method
     * @param string           $docLine
     * @param string           $description
     *
     * @return null|RuntimeHook
     */
    public function readCallee($contextClass, ReflectionMethod $method, $docLine, $description)
    {
        if (!preg_match(self::$regex, $docLine, $match)) {
            return null;
        }

        $type = strtolower($match[1]);
        $class = self::$classes[$type];
        $pattern = isset($match[2]) ? $match[2] : null;
        $callable = array($contextClass, $method->getName());

        return new $class($pattern, $callable, $description);
    }
}
