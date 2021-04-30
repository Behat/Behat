<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Context\Attribute;

use ReflectionMethod;

/**
 * Reads Attributes of a provided context method into a Callee.
 *
 * @see AttributeContextReader
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface AttributeReader
{
    /**
     * Reads all callees associated with a provided method.
     *
     * @param string           $contextClass
     * @param ReflectionMethod $method
     *
     * @return array
     */
    public function readCallees(string $contextClass, ReflectionMethod $method);
}
