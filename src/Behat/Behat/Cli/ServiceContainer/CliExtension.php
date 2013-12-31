<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Cli\ServiceContainer;

use Behat\Testwork\Cli\ServiceContainer\CliExtension as BaseExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Behat cli extension.
 *
 * Uses custom printer.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class CliExtension extends BaseExtension
{
    /**
     * Loads output printer.
     *
     * @param ContainerBuilder $container
     */
    protected function loadOutputPrinter(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Behat\Cli\Printer\CliOutputPrinter');
        $container->setDefinition(self::OUTPUT_PRINTER_ID, $definition);
    }
}
