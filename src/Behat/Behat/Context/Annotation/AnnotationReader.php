<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Context\Annotation;

use Behat\Behat\Context\Reader\AnnotatedContextReader;
use Behat\Testwork\Call\Callee;
use ReflectionMethod;

/**
 * Context annotation reader interface.
 *
 * Reads custom annotation of a provided context method into a Callee. Used by AnnotatedContextReader.
 *
 * @see AnnotatedContextReader
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface AnnotationReader
{
    /**
     * Loads custom callees associated with a provided method.
     *
     * @param string           $contextClass
     * @param ReflectionMethod $method
     * @param string           $docLine
     * @param string           $description
     *
     * @return null|Callee
     */
    public function readCallee($contextClass, ReflectionMethod $method, $docLine, $description);
}
