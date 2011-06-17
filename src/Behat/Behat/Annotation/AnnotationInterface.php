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
     * Returns path string for callback.
     *
     * @return  string
     */
    function getPath();

    /**
     * Checks whether callback is closure.
     *
     * @return  Boolean
     */
    function isClosure();

    /**
     * Returns callback.
     *
     * @param   Callback
     */
    function getCallback();

    /**
     * Returns callback reflection.
     *
     * @return  ReflectionFunction
     */
    function getCallbackReflection();
}
