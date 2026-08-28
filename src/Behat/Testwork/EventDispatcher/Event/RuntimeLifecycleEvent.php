<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\EventDispatcher\Event;

use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Event\Event;
use Behat\Testwork\Suite\Suite;

/**
 * Base implementation for events which hold references to current suite and environment.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * @internal Behat's own events extend this. Third parties should implement {@see LifecycleEvent}.
 */
abstract class RuntimeLifecycleEvent extends Event implements LifecycleEvent
{
    /**
     * Initializes scenario event.
     */
    public function __construct(
        private readonly Environment $environment,
    ) {
    }

    public function getSuite(): Suite
    {
        return $this->environment->getSuite();
    }

    public function getEnvironment(): Environment
    {
        return $this->environment;
    }
}
