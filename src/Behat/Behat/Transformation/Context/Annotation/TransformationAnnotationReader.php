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
use Behat\Behat\Transformation\Transformation;
use Behat\Behat\Transformation\Transformation\PatternTransformation;

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
     * @param string $contextClass
     * @param string $docLine
     * @param string $description
     *
     * @return null|Transformation
     */
    public function readCallee($contextClass, \ReflectionMethod $method, $docLine, $description)
    {
        if (!preg_match(self::$regex, $docLine, $match)) {
            return null;
        }

        $pattern = $match[1];
        $callable = [$contextClass, $method->getName()];

        foreach ($this->simpleTransformations() as $transformation) {
            if ($transformation::supportsPatternAndMethod($pattern, $method)) {
                return new $transformation($pattern, $callable, $description);
            }
        }

        return new PatternTransformation($pattern, $callable, $description);
    }

    /**
     * Returns list of default transformations.
     *
     * @return array
     */
    private function simpleTransformations()
    {
        return [
            'Behat\Behat\Transformation\Transformation\RowBasedTableTransformation',
            'Behat\Behat\Transformation\Transformation\ColumnBasedTableTransformation',
            'Behat\Behat\Transformation\Transformation\TableRowTransformation',
            'Behat\Behat\Transformation\Transformation\TokenNameAndReturnTypeTransformation',
            'Behat\Behat\Transformation\Transformation\ReturnTypeTransformation',
            'Behat\Behat\Transformation\Transformation\TokenNameTransformation',
        ];
    }
}
