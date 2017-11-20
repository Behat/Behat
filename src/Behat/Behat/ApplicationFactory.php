<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat;

use Behat\Behat\Context\ServiceContainer\ContextExtension;
use Behat\Behat\Definition\ServiceContainer\DefinitionExtension;
use Behat\Behat\EventDispatcher\ServiceContainer\EventDispatcherExtension;
use Behat\Behat\Gherkin\ServiceContainer\GherkinExtension;
use Behat\Behat\Hook\ServiceContainer\HookExtension;
use Behat\Behat\Output\ServiceContainer\Formatter\JUnitFormatterFactory;
use Behat\Behat\Output\ServiceContainer\Formatter\PrettyFormatterFactory;
use Behat\Behat\Output\ServiceContainer\Formatter\ProgressFormatterFactory;
use Behat\Behat\HelperContainer\ServiceContainer\HelperContainerExtension;
use Behat\Behat\Snippet\ServiceContainer\SnippetExtension;
use Behat\Behat\Tester\ServiceContainer\TesterExtension;
use Behat\Behat\Transformation\ServiceContainer\TransformationExtension;
use Behat\Behat\Translator\ServiceContainer\GherkinTranslationsExtension;
use Behat\Testwork\ApplicationFactory as BaseFactory;
use Behat\Testwork\Argument\ServiceContainer\ArgumentExtension;
use Behat\Testwork\Autoloader\ServiceContainer\AutoloaderExtension;
use Behat\Testwork\Call\ServiceContainer\CallExtension;
use Behat\Testwork\Cli\ServiceContainer\CliExtension;
use Behat\Testwork\Environment\ServiceContainer\EnvironmentExtension;
use Behat\Testwork\Exception\ServiceContainer\ExceptionExtension;
use Behat\Testwork\Filesystem\ServiceContainer\FilesystemExtension;
use Behat\Testwork\Ordering\ServiceContainer\OrderingExtension;
use Behat\Testwork\Output\ServiceContainer\Formatter\FormatterFactory;
use Behat\Testwork\Output\ServiceContainer\OutputExtension;
use Behat\Testwork\ServiceContainer\ServiceProcessor;
use Behat\Testwork\Specification\ServiceContainer\SpecificationExtension;
use Behat\Testwork\Suite\ServiceContainer\SuiteExtension;
use Behat\Testwork\Translator\ServiceContainer\TranslatorExtension;

/**
 * Defines the way behat is created.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class ApplicationFactory extends BaseFactory
{
    const VERSION = '3.4.2';

    /**
     * {@inheritdoc}
     */
    protected function getName()
    {
        return 'behat';
    }

    /**
     * {@inheritdoc}
     */
    protected function getVersion()
    {
        return self::VERSION;
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultExtensions()
    {
        $processor = new ServiceProcessor();

        return array(
            new ArgumentExtension(),
            new AutoloaderExtension(array('' => '%paths.base%/features/bootstrap')),
            new SuiteExtension($processor),
            new OutputExtension('pretty', $this->getDefaultFormatterFactories($processor), $processor),
            new ExceptionExtension($processor),
            new GherkinExtension($processor),
            new CallExtension($processor),
            new TranslatorExtension(),
            new GherkinTranslationsExtension(),
            new TesterExtension($processor),
            new CliExtension($processor),
            new EnvironmentExtension($processor),
            new SpecificationExtension($processor),
            new FilesystemExtension(),
            new ContextExtension($processor),
            new SnippetExtension($processor),
            new DefinitionExtension($processor),
            new EventDispatcherExtension($processor),
            new HookExtension(),
            new TransformationExtension($processor),
            new OrderingExtension($processor),
            new HelperContainerExtension($processor)
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getEnvironmentVariableName()
    {
        return 'BEHAT_PARAMS';
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfigPath()
    {
        $cwd = rtrim(getcwd(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $configDir = $cwd . 'config' . DIRECTORY_SEPARATOR;
        $paths = array(
            $cwd . 'behat.yaml',
            $cwd . 'behat.yml',
            $cwd . 'behat.yaml.dist',
            $cwd . 'behat.yml.dist',
            $configDir . 'behat.yaml',
            $configDir . 'behat.yml',
            $configDir . 'behat.yaml.dist',
            $configDir . 'behat.yml.dist',
        );

        foreach ($paths as $path) {
            if (is_file($path)) {
                return $path;
            }
        }

        return null;
    }

    /**
     * Returns default formatter factories.
     *
     * @param ServiceProcessor $processor
     *
     * @return FormatterFactory[]
     */
    private function getDefaultFormatterFactories(ServiceProcessor $processor)
    {
        return array(
            new PrettyFormatterFactory($processor),
            new ProgressFormatterFactory($processor),
            new JUnitFormatterFactory(),
        );
    }
}
