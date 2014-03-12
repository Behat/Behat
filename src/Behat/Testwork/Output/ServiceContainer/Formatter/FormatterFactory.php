<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Output\ServiceContainer\Formatter;

use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Provides a way to easily define custom formatters.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface FormatterFactory
{
    /**
     * Builds formatter configuration.
     *
     * @param ContainerBuilder $container
     */
    public function buildFormatter(ContainerBuilder $container);

    /**
     * Processes formatter configuration.
     *
     * @param ContainerBuilder $container
     */
    public function processFormatter(ContainerBuilder $container);
}
