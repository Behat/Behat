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
 * Annotation reader interface.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface AnnotationReaderInterface
{
    /**
     * Loads step callees (if exist) associated with specific method.
     *
     * @param ReflectionMethod $method
     * @param string           $docLine
     * @param string           $description
     *
     * @return CalleeInterface[]
     */
    public function readAnnotation(ReflectionMethod $method, $docLine, $description);
}
