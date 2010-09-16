<?php

namespace Everzet\Behat\Environment;

/*
 * This file is part of the behat package.
 * (c) 2010 Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Environment container/loader interface.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface EnvironmentInterface
{
    /**
     * Loads environment configuration from env.php (or different env file)
     * 
     * @param     string  $file       file path to include initializations from
     */
    public function loadEnvironmentFile($envFile);
}
