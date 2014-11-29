<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Tester;

use Behat\Testwork\Tester\Context\Context;

/**
 * Represents a run control for tests.
 *
 * Controls the test execution flow.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface RunControl
{
    /**
     * Checks if provided context is testable or should be skipped.
     *
     * @param Context $context
     *
     * @return Boolean
     */
    public function isContextTestable(Context $context);
}
