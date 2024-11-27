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
use Behat\Behat\Transformation\Transformation\PatternTransformation;
use Behat\Behat\Transformation\Transformation;
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
    /**
     * @var string
     */
    private static $regex = '/^\@transform\s*+(.*+)$/i';

    /**
     * Loads step callees (if exist) associated with specific method.
     *
     * @param string           $contextClass
     * @param ReflectionMethod $method
     * @param string           $docLine
     * @param string           $description
     */
    public function readCallee($contextClass, ReflectionMethod $method, $docLine, $description): ?Transformation
    {
        if (!preg_match(self::$regex, $docLine, $match)) {
            return null;
        }

        $pattern = $match[1];

        return TransformationCalleeFactory::create($contextClass, $method, $pattern, $description);
    }
}
