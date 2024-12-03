<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Transformation\Context\Factory;

use Behat\Behat\Transformation\SimpleArgumentTransformation;
use Behat\Behat\Transformation\Transformation;
use Behat\Behat\Transformation\Transformation\ColumnBasedTableTransformation;
use Behat\Behat\Transformation\Transformation\PatternTransformation;
use Behat\Behat\Transformation\Transformation\ReturnTypeTransformation;
use Behat\Behat\Transformation\Transformation\RowBasedTableTransformation;
use Behat\Behat\Transformation\Transformation\TableRowTransformation;
use Behat\Behat\Transformation\Transformation\TokenNameAndReturnTypeTransformation;
use Behat\Behat\Transformation\Transformation\TokenNameTransformation;
use ReflectionMethod;

/**
 * Generates the callee for a transformation
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * @internal
 */
class TransformationCalleeFactory
{
    public static function create(string $contextClass, ReflectionMethod $method, string $pattern, ?string $description): Transformation
    {
        $callable = array($contextClass, $method->getName());

        foreach (self::simpleTransformations() as $transformation) {
            if ($transformation::supportsPatternAndMethod($pattern, $method)) {
                return new $transformation($pattern, $callable, $description);
            }
        }

        return new PatternTransformation($pattern, $callable, $description);
    }

    /**
     * Returns list of default transformations.
     *
     * @return class-string<SimpleArgumentTransformation>[]
     */
    private static function simpleTransformations()
    {
        return [
            RowBasedTableTransformation::class,
            ColumnBasedTableTransformation::class,
            TableRowTransformation::class,
            TokenNameAndReturnTypeTransformation::class,
            ReturnTypeTransformation::class,
            TokenNameTransformation::class
        ];
    }
}
