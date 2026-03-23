<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Transformation\Context\Annotation;

use Behat\Behat\Context\Annotation\AnnotationReader;
use Behat\Behat\Transformation\Context\Factory\TransformationCalleeFactory;
use Behat\Behat\Transformation\Transformation;
use Behat\Testwork\Deprecation\DeprecationCollector;
use ReflectionMethod;

/**
 * Step transformation annotation reader.
 *
 * Reads step transformations from a context method annotation.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class TransformationAnnotationReader implements AnnotationReader
{
    private static string $regex = '/^\@transform\s*+(.*+)$/i';

    /**
     * Loads step callees (if exist) associated with specific method.
     *
     * @param string           $contextClass
     * @param string           $docLine
     * @param string           $description
     */
    public function readCallee($contextClass, ReflectionMethod $method, $docLine, $description): ?Transformation
    {
        if (!preg_match(self::$regex, $docLine, $match)) {
            return null;
        }

        DeprecationCollector::trigger('Using annotations to define transformations is deprecated and will be removed in Behat 4.0. Use PHP attributes instead.');

        $pattern = $match[1];

        return TransformationCalleeFactory::create($contextClass, $method, $pattern, $description);
    }
}
