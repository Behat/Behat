<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\HelperContainer\Environment;

use Behat\Testwork\Environment\Environment;
use Psr\Container\ContainerInterface;

/**
 * Represents test environment based on a service locator pattern.
 *
 * @see ContextEnvironmentHandler
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface ServiceContainerEnvironment extends Environment
{
    /**
     * Sets/unsets service container for the environment.
     *
     * @param ContainerInterface|null $container
     */
    public function setServiceContainer(ContainerInterface $container = null);

    /**
     * Returns environment service container if set.
     *
     * @return null|ContainerInterface
     */
    public function getServiceContainer();
}
