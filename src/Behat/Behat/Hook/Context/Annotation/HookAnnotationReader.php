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
use Behat\Behat\Hook\Call\AfterFeature;
use Behat\Behat\Hook\Call\AfterScenario;
use Behat\Behat\Hook\Call\AfterStep;
use Behat\Behat\Hook\Call\BeforeFeature;
use Behat\Behat\Hook\Call\BeforeScenario;
use Behat\Behat\Hook\Call\BeforeStep;
use Behat\Testwork\Hook\Call\AfterSuite;
use Behat\Testwork\Hook\Call\BeforeSuite;
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
    private static $classes = [
        'beforesuite' => BeforeSuite::class,
        'aftersuite' => AfterSuite::class,
        'beforefeature' => BeforeFeature::class,
        'afterfeature' => AfterFeature::class,
        'beforescenario' => BeforeScenario::class,
        'afterscenario' => AfterScenario::class,
        'beforestep' => BeforeStep::class,
        'afterstep' => AfterStep::class,
    ];

    /**
     * Loads step callees (if exist) associated with specific method.
     *
     * @param string           $contextClass
     * @param string           $docLine
     * @param string           $description
     *
     * @return RuntimeHook|null
     */
    public function readCallee($contextClass, ReflectionMethod $method, $docLine, $description)
    {
        if (!preg_match(self::$regex, $docLine, $match)) {
            return null;
        }

        $type = strtolower($match[1]);
        $class = self::$classes[$type];
        $pattern = $match[2] ?? null;
        $callable = [$contextClass, $method->getName()];

        return new $class($pattern, $callable, $description);
    }
}
