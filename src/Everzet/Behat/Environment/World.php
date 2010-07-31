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
 * World container interface.
 *
 * @package     behat
 * @subpackage  Behat
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface World
{
    /**
     * Sets environment config file
     *
     * @return  void
     */
    static public function setEnvFile($envFile);
}
