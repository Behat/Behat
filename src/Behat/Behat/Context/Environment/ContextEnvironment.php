<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Context\Environment;

use Behat\Behat\Context\Environment\Handler\ContextEnvironmentHandler;
use Behat\Behat\Context\Pool\ContextPool;
use Behat\Testwork\Environment\Environment;

/**
 * Context-based environment interface.
 *
 * Test environment based on a context objects.
 *
 * @see ContextEnvironmentHandler
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface ContextEnvironment extends Environment
{
    /**
     * Returns context pool.
     *
     * @return ContextPool
     */
    public function getContextPool();
}
