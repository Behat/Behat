<?php

namespace Behat\Behat\Annotation;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Behat annotation interface.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface AnnotationInterface
{
    /**
     * Returns callback.
     *
     * @param   Callback
     */
    function getCallback();

    /**
     * Returns definition class name.
     *
     * @return  string
     */
    function getClass();

    /**
     * Returns definition method name.
     *
     * @return  string
     */
    function getMethod();

    /**
     * Returns callback reflection for definition matcher.
     *
     * @return  ReflectionFunction
     */
    function getCallbackReflection();
}
