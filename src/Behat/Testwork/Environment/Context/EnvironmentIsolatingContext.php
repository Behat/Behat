<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Environment\Context;

use Behat\Testwork\Environment\EnvironmentManager;
use Behat\Testwork\Tester\Context\TestContext;

/**
 * Represents a context that could be isolated using environment manager.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface EnvironmentIsolatingContext extends TestContext
{
    /**
     * Creates a new context instance with provided environment.
     *
     * @param EnvironmentManager $environmentManager
     *
     * @return TestContext
     */
    public function createIsolatedContext(EnvironmentManager $environmentManager);
}
