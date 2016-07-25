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
use Behat\Behat\Transformation\Call\RowBasedTableTransformation;
use Behat\Behat\Transformation\Call\RuntimeTransformation;
use Behat\Behat\Transformation\Call\ColumnBasedTableTransformation;
use Behat\Behat\Transformation\Call\TableRowTransformation;
use Behat\Behat\Transformation\Call\TokenNameTransformation;
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
    private static $regex = '/^\@transform\s+(.+)$/i';

    /**
     * Loads step callees (if exist) associated with specific method.
     *
     * @param string           $contextClass
     * @param ReflectionMethod $method
     * @param string           $docLine
     * @param string           $description
     *
     * @return null|Transformation
     */
    public function readCallee($contextClass, ReflectionMethod $method, $docLine, $description)
    {
        if (!preg_match(self::$regex, $docLine, $match)) {
            return null;
        }

        $pattern = $match[1];
        $callable = array($contextClass, $method->getName());

        if (1 === preg_match(TokenNameTransformation::PATTERN_REGEX, $pattern)) {
            return new TokenNameTransformation($pattern, $callable, $description);
        }

        if (1 === preg_match(ColumnBasedTableTransformation::PATTERN_REGEX, $pattern)) {
            return new ColumnBasedTableTransformation($pattern, $callable, $description);
        }

        if (1 === preg_match(RowBasedTableTransformation::PATTERN_REGEX, $pattern)) {
            return new RowBasedTableTransformation($pattern, $callable, $description);
        }

        if (1 === preg_match(TableRowTransformation::PATTERN_REGEX, $pattern)) {
            return new TableRowTransformation($pattern, $callable, $description);
        }

        return new RuntimeTransformation($pattern, $callable, $description);
    }
}
