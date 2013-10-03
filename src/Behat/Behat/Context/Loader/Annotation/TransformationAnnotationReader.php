<?php

namespace Behat\Behat\Context\Loader\Annotation;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Behat\Behat\Callee\CalleeInterface;
use Behat\Behat\Transformation\Callee\Transformation;
use ReflectionMethod;

/**
 * Step transformation annotation reader.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class TransformationAnnotationReader implements AnnotationReaderInterface
{
    /**
     * @var string
     */
    private static $regex = '/^\@transform\s+(.+)$/i';

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
        if (!preg_match(self::$regex, $docLine, $match)) {
            return null;
        }

        $pattern = $match[1];
        $callable = array($method->getDeclaringClass()->getName(), $method->getName());

        return new Transformation($pattern, $callable, $description);
    }
}
