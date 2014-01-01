<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat;

use Behat\Behat\Autoloader\ServiceContainer\AutoloaderExtension;
use Behat\Behat\Context\ServiceContainer\ContextExtension;
use Behat\Behat\Definition\ServiceContainer\DefinitionExtension;
use Behat\Behat\Gherkin\ServiceContainer\GherkinExtension;
use Behat\Behat\Hook\ServiceContainer\HookExtension;
use Behat\Behat\Output\ServiceContainer\OutputExtension;
use Behat\Behat\Snippet\ServiceContainer\SnippetExtension;
use Behat\Behat\Tester\ServiceContainer\TesterExtension;
use Behat\Behat\Transformation\ServiceContainer\TransformationExtension;
use Behat\Behat\Translator\ServiceContainer\TranslatorExtension;
use Behat\Testwork\ApplicationFactory as BaseFactory;
use Behat\Testwork\Call\ServiceContainer\CallExtension;
use Behat\Testwork\Cli\ServiceContainer\CliExtension;
use Behat\Testwork\Environment\ServiceContainer\EnvironmentExtension;
use Behat\Testwork\EventDispatcher\ServiceContainer\EventDispatcherExtension;
use Behat\Testwork\Exception\ServiceContainer\ExceptionExtension;
use Behat\Testwork\Filesystem\ServiceContainer\FilesystemExtension;
use Behat\Testwork\ServiceContainer\Extension;
use Behat\Testwork\ServiceContainer\ServiceProcessor;
use Behat\Testwork\Subject\ServiceContainer\SubjectExtension;
use Behat\Testwork\Suite\ServiceContainer\SuiteExtension;

/**
 * Behat application factory.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class ApplicationFactory extends BaseFactory
{
    const VERSION = '3.0-dev';

    /**
     * Returns application name.
     *
     * @return string
     */
    protected function getName()
    {
        return 'behat';
    }

    /**
     * Returns current application version.
     *
     * @return string
     */
    protected function getVersion()
    {
        return self::VERSION;
    }

    /**
     * Returns list of extensions enabled by default.
     *
     * @return Extension[]
     */
    protected function getDefaultExtensions()
    {
        $processor = new ServiceProcessor();

        return array(
            // Testwork extensions
            new CliExtension($processor),
            new CallExtension($processor),
            new SuiteExtension($processor),
            new EnvironmentExtension($processor),
            new SubjectExtension($processor),
            new EventDispatcherExtension($processor),
            new FilesystemExtension($processor),
            new ExceptionExtension($processor),

            // Behat extensions
            new AutoloaderExtension($processor),
            new TranslatorExtension($processor),
            new GherkinExtension($processor),
            new ContextExtension($processor),
            new OutputExtension($processor),
            new SnippetExtension($processor),
            new DefinitionExtension($processor),
            new HookExtension($processor),
            new TransformationExtension($processor),
            new TesterExtension($processor),
        );
    }

    /**
     * Returns the name of configuration environment variable.
     *
     * @return string
     */
    protected function getEnvironmentVariableName()
    {
        return 'BEHAT_PARAMS';
    }

    /**
     * Returns user config path.
     *
     * @return null|string
     */
    protected function getConfigPath()
    {
        $cwd = rtrim(getcwd(), DIRECTORY_SEPARATOR);
        $paths = array_filter(
            array(
                $cwd . DIRECTORY_SEPARATOR . 'behat.yml',
                $cwd . DIRECTORY_SEPARATOR . 'behat.yml.dist',
                $cwd . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'behat.yml',
                $cwd . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'behat.yml.dist',
            ),
            'is_file'
        );

        if (count($paths)) {
            return current($paths);
        }

        return null;
    }
}
