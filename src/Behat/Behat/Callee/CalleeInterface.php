<?php

namespace Behat\Behat\Callee;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use ReflectionFunction;
use ReflectionMethod;

/**
 * Callee interface.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface CalleeInterface
{
    /**
     * Returns callee definition path.
     *
     * @return string
     */
    public function getPath();

    /**
     * Returns callee description.
     *
     * @return string
     */
    public function getDescription();

    /**
     * Returns true if callee is a method, false otherwise.
     *
     * @return Boolean
     */
    public function isMethod();

    /**
     * Returns callable.
     *
     * @return Callable
     */
    public function getCallable();

    /**
     * Returns callable reflection.
     *
     * @return ReflectionFunction|ReflectionMethod
     */
    public function getReflection();
}
