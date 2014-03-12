<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\EventDispatcher\Event;

use Behat\Testwork\Tester\Setup\Setup;

/**
 * Represents an event right before a test.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface BeforeTested
{
    /**
     * Returns current test setup.
     *
     * @return Setup
     */
    public function getSetup();
}
