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
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface AnnotationInterface
{
    /**
     * Returns path string for callback.
     *
     * @return string
     */
    public function getPath();

    /**
     * Checks whether callback is closure.
     *
     * @return Boolean
     */
    public function isClosure();

    /**
     * Returns callback.
     *
     * @return callback
     */
    public function getCallback();

    /**
     * Returns callback reflection.
     *
     * @return \ReflectionFunction
     */
    public function getCallbackReflection();
}
