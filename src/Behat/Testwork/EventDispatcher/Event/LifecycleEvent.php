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
 * Represents an event which holds references to current suite and environment.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
abstract class LifecycleEvent extends Event
{
    /**
     * @var Environment
     */
    private $environment;

    /**
     * Initializes scenario event.
     *
     * @param Environment $env
     */
    public function __construct(Environment $env)
    {
        $this->environment = $env;
    }

    /**
     * Returns suite in which this event was fired.
     *
     * @return Suite
     */
    public function getSuite()
    {
        return $this->environment->getSuite();
    }

    /**
     * Returns environment in which this event was fired.
     *
     * @return Environment
     */
    public function getEnvironment()
    {
        return $this->environment;
    }
}
