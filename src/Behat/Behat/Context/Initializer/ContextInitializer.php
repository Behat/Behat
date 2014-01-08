<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Context\Initializer;

use Behat\Behat\Context\Context;
use Behat\Behat\Context\Environment\Handler\ContextEnvironmentHandler;

/**
 * Context initializer interface.
 *
 * Initializes custom contexts. Used by ContextEnvironmentHandler.
 *
 * @see ContextEnvironmentHandler
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface ContextInitializer
{
    /**
     * Initializes provided context.
     *
     * @param Context $context
     */
    public function initializeContext(Context $context);
}
