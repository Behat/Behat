<?php

namespace Everzet\Behat\Runner;

/*
 * This file is part of the behat package.
 * (c) 2010 Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Runners interface.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface RunnerInterface
{
    /**
     * Runs test runner
     *
     * @return  integer     status code
     */
    public function run();

    /**
     * Returns string representation of runner status code
     *
     * @return  string  status
     */
    public function getStatus();

    /**
     * Returns runner status code
     *
     * @return  integer status code
     */
    public function getStatusCode();
}
