<?php

namespace Behat\Behat\Console;

use Symfony\Component\DependencyInjection\ContainerBuilder,
    Symfony\Component\DependencyInjection\ContainerInterface,
    Symfony\Component\Console\Application,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Input\InputDefinition,
    Symfony\Component\Console\Input\InputOption,
    Symfony\Component\Console\Output\OutputInterface;

use Behat\Behat\DependencyInjection\BehatExtension,
    Behat\Behat\DependencyInjection\Compiler\GherkinPass,
    Behat\Behat\DependencyInjection\Compiler\FormattersPass,
    Behat\Behat\DependencyInjection\Compiler\ContextReaderPass,
    Behat\Behat\DependencyInjection\Compiler\EventDispatcherPass,
    Behat\Behat\DependencyInjection\Compiler\CommandProcessorsPass,
    Behat\Behat\Extension\ExtensionInterface;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Behat console application.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class BehatApplication extends Application
{
    private $configPath;

    /**
     * {@inheritdoc}
     */
    public function __construct($version)
    {
        parent::__construct('Behat', $version);
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultInputDefinition()
    {
        return new InputDefinition(array(
            new InputOption('--help',    '-h', InputOption::VALUE_NONE, 'Display this help message.'),
            new InputOption('--verbose', '-v', InputOption::VALUE_NONE, 'Increase verbosity of exceptions.'),
            new InputOption('--version', '-V', InputOption::VALUE_NONE, 'Display this behat version.'),
            new InputOption('--config',  '-c', InputOption::VALUE_REQUIRED, 'Specify config file to use.'),
            new InputOption('--profile', '-p', InputOption::VALUE_REQUIRED, 'Specify config profile to use.')
        ));
    }

    /**
     * Runs the current application.
     *
     * @param InputInterface  $input  An Input instance
     * @param OutputInterface $output An Output instance
     *
     * @return integer 0 if everything went fine, or an error code
     */
    public function doRun(InputInterface $input, OutputInterface $output)
    {
        // construct container
        $container = new ContainerBuilder();
        $this->configureContainer($container, $input);
        $this->loadExtensions($container);

        // add core compiler passes
        $container->addCompilerPass(new GherkinPass());
        $container->addCompilerPass(new CommandProcessorsPass());
        $container->addCompilerPass(new FormattersPass());
        $container->addCompilerPass(new ContextReaderPass());
        $container->addCompilerPass(new EventDispatcherPass());

        // compile and freeze container
        $container->compile();

        // setup command into application
        $this->add($container->get('behat.command'));

        return parent::doRun($input, $output);
    }

    /**
     * {@inheritdoc}
     */
    protected function getCommandName(InputInterface $input)
    {
        return 'behat';
    }

    /**
     * {@inheritdoc}
     */
    protected function getTerminalWidth()
    {
        return PHP_INT_MAX;
    }

    /**
     * Configures container based on providen config file and profile.
     *
     * @param ContainerInterface $container container instance
     * @param InputInterface     $input     console input
     */
    protected function configureContainer(ContainerInterface $container, InputInterface $input)
    {
        $extension  = new BehatExtension();
        $cwd        = getcwd();
        $configFile = $input->getParameterOption(array('--config', '-c'));
        $profile    = $input->getParameterOption(array('--profile', '-p')) ?: 'default';
        $configs    = array();

        if (!$configFile) {
            if (is_file($cwd.DIRECTORY_SEPARATOR.'behat.yml')) {
                $configFile = $cwd.DIRECTORY_SEPARATOR.'behat.yml';
            } elseif (is_file($cwd.DIRECTORY_SEPARATOR.'behat.yml.dist')) {
                $configFile = $cwd.DIRECTORY_SEPARATOR.'behat.yml.dist';
            } elseif (is_file($cwd.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'behat.yml')) {
                $configFile = $cwd.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'behat.yml';
            } elseif (is_file($cwd.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'behat.yml.dist')) {
                $configFile = $cwd.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'behat.yml.dist';
            }
        }

        // read and normalize raw parameters string from env
        if ($config = getenv('BEHAT_PARAMS')) {
            parse_str($config, $config);
            $configs[] = $this->normalizeRawConfiguration($config);
        }

        // read configuration file
        if (file_exists($configFile)) {
            $configs[] = $extension->readConfigurationFile($configFile, $profile, $container);
        }

        // configure container
        $extension->load($configs, $container);
        $container->addObjectResource($extension);

        if (file_exists($configFile)) {
            $this->configPath  = dirname($configFile);
            $pathLocator = $container->getDefinition('behat.path_locator');
            $pathLocator->addMethodCall('setPathConstant', array(
                'BEHAT_CONFIG_PATH', dirname($configFile)
            ));
        }
    }

    /**
     * Loads Behat extensions.
     *
     * @param ContainerBuilder $container
     */
    public function loadExtensions(ContainerBuilder $container)
    {
        foreach ($container->getParameter('behat.extensions') as $id => $config) {
            if ($this->configPath) {
                $id = str_replace('%%BEHAT_CONFIG_PATH%%', $this->configPath, $id);
            }

            $extension = null;
            if (class_exists($id)) {
                $extension = new $id;
            } elseif (file_exists($id)) {
                $extension = require($id);
            } elseif ($this->configPath && file_exists($this->configPath.DIRECTORY_SEPARATOR.$id)) {
                $extension = require($this->configPath.DIRECTORY_SEPARATOR.$id);
            } else {
                foreach (explode(':', get_include_path()) as $libPath) {
                    if (file_exists($libPath.DIRECTORY_SEPARATOR.$id)) {
                        $extension = require($libPath.DIRECTORY_SEPARATOR.$id);
                        break;
                    }
                }
            }

            if (null === $extension) {
                throw new \InvalidArgumentException(sprintf(
                    '"%s" extension could not be initialized.', $id
                ));
            }
            if (!$extension instanceof ExtensionInterface) {
                throw new \InvalidArgumentException(sprintf(
                    '"%s" extension should implement ExtensionInterface.', $id
                ));
            }

            $extension->load($config, $container);
        }
    }

    /**
     * Normalizes provided raw configuration.
     *
     * @param array $config raw configuration
     *
     * @return array
     */
    private function normalizeRawConfiguration(array $config)
    {
        $normalize = function($value) {
            if ('true' === $value || 'false' === $value) {
                return 'true' === $value;
            }

            if (is_numeric($value)) {
                return ctype_digit($value) ? intval($value) : floatval($value);
            }

            return $value;
        };

        if (isset($config['formatter']['parameters'])) {
            $config['formatter']['parameters'] = array_map(
                $normalize, $config['formatter']['parameters']
            );
        }

        if (isset($config['context']['parameters'])) {
            $config['context']['parameters'] = array_map(
                $normalize, $config['context']['parameters']
            );
        }

        return $config;
    }
}
