<?php

namespace Behat\Behat\Environment;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Environment interface.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface EnvironmentInterface
{
    /**
     * Sets environment parameter.
     *
     * @param   string  $name
     * @param   mixed   $value
     */
    function setParameter($name, $value);

    /**
     * Returns environment parameter.
     *
     * @param   string  $name
     *
     * @return  mixed
     */
    function getParameter($name);

    /**
     * Loads environment resource (configuration).
     *
     * @param     string  $resource     resource path
     */
    function loadEnvironmentResource($resource);
}
