<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Definition\Context\Annotation;

use Behat\Behat\Context\Annotation\AnnotationReader;
use Behat\Behat\Definition\Call\Given;
use Behat\Behat\Definition\Call\RuntimeDefinition;
use Behat\Behat\Definition\Call\Then;
use Behat\Behat\Definition\Call\When;
use Behat\Testwork\Deprecation\DeprecationCollector;
use ReflectionMethod;

/**
 * Reads definition annotations from the context class.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class DefinitionAnnotationReader implements AnnotationReader
{
    /**
     * @var string
     */
    private static $regex = '/^\@(given|when|then)\s+(.+)$/i';
    /**
     * @var string[]
     */
    private static $classes = [
        'given' => Given::class,
        'when' => When::class,
        'then' => Then::class,
    ];

    public function readCallee($contextClass, ReflectionMethod $method, $docLine, $description): ?RuntimeDefinition
    {
        if (!preg_match(self::$regex, $docLine, $match)) {
            return null;
        }

        DeprecationCollector::trigger('Using annotations to define steps is deprecated and will be removed in Behat 4.0. Use PHP attributes instead.');

        $type = strtolower($match[1]);
        $class = self::$classes[$type];
        $pattern = $match[2];
        $callable = [$contextClass, $method->getName()];

        return new $class($pattern, $callable, $description);
    }
}
