<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Output\ServiceContainer;

use Behat\Behat\Definition\ServiceContainer\DefinitionExtension;
use Behat\Testwork\Exception\ServiceContainer\ExceptionExtension;
use Behat\Testwork\Output\ServiceContainer\OutputExtension as BaseExtension;
use Behat\Testwork\Translator\ServiceContainer\TranslatorExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Behat output extension.
 *
 * Extends testwork extension with default formatters.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class OutputExtension extends BaseExtension
{
    /**
     * Loads default formatters.
     *
     * @param ContainerBuilder $container
     */
    protected function loadFormatters(ContainerBuilder $container)
    {
        $this->loadPrettyFormatter($container);
        $this->loadProgressFormatter($container);
    }

    /**
     * Returns default formatter name.
     *
     * @return string
     */
    protected function getDefaultFormatterName()
    {
        return 'pretty';
    }

    /**
     * Loads pretty formatter.
     *
     * @param ContainerBuilder $container
     */
    protected function loadPrettyFormatter(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Behat\Output\PrettyFormatter', array(
            $this->createOutputPrinterDefinition(),
            new Reference(ExceptionExtension::PRESENTER_ID),
            new Reference(TranslatorExtension::TRANSLATOR_ID),
            new Reference(DefinitionExtension::PATTERN_TRANSFORMER_ID),
            '%paths.base%'
        ));
        $definition->addTag(self::FORMATTER_TAG, array('priority' => 100));
        $container->setDefinition(self::FORMATTER_TAG . '.pretty', $definition);
    }

    /**
     * Loads progress formatter.
     *
     * @param ContainerBuilder $container
     */
    protected function loadProgressFormatter(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Behat\Output\ProgressFormatter', array(
            $this->createOutputPrinterDefinition(),
            new Reference(ExceptionExtension::PRESENTER_ID),
            new Reference(TranslatorExtension::TRANSLATOR_ID),
            new Reference(DefinitionExtension::PATTERN_TRANSFORMER_ID),
            '%paths.base%'
        ));
        $definition->addTag(self::FORMATTER_TAG, array('priority' => 100));
        $container->setDefinition(self::FORMATTER_TAG . '.progress', $definition);
    }

    /**
     * Creates output printer definition.
     *
     * @return Definition
     */
    protected function createOutputPrinterDefinition()
    {
        return new Definition('Behat\Behat\Output\Printer\ConsoleOutputPrinter');
    }
}
