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
     * Loads environment resource (configuration).
     *
     * @param     string  $resource     resource path
     */
    function loadEnvironmentResource($resource);
}
