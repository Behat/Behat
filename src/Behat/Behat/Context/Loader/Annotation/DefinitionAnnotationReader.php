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
use ReflectionMethod;

/**
 * Step definitions annotation reader.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class DefinitionAnnotationReader implements AnnotationReaderInterface
{
    /**
     * @var string
     */
    private static $regex = '/^\@(given|when|then)\s+(.+)$/i';
    /**
     * @var string[]
     */
    private static $classes = array(
        'given' => 'Behat\Behat\Definition\Callee\Given',
        'when'  => 'Behat\Behat\Definition\Callee\When',
        'then'  => 'Behat\Behat\Definition\Callee\Then',
    );

    /**
     * Loads step callees (if exist) associated with specific method.
     *
     * @param ReflectionMethod $method
     * @param string           $docLine
     * @param string           $description
     *
     * @return CalleeInterface[]
     */
    public function readAnnotation(ReflectionMethod $method, $docLine, $description)
    {
        if (preg_match(self::$regex, $docLine, $match)) {
            $type = strtolower($match[1]);
            $class = self::$classes[$type];
            $pattern = $match[2];
            $callable = array($method->getDeclaringClass()->getName(), $method->getName());

            return array(new $class($pattern, $callable, $description));
        }

        return array();
    }
}
