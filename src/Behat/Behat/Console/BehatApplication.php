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
    Behat\Behat\DependencyInjection\Configuration\Loader,
    Behat\Behat\DependencyInjection\Compiler\GherkinLoadersPass,
    Behat\Behat\DependencyInjection\Compiler\FormattersPass,
    Behat\Behat\DependencyInjection\Compiler\ContextLoadersPass,
    Behat\Behat\DependencyInjection\Compiler\EventSubscribersPass,
    Behat\Behat\DependencyInjection\Compiler\ConsoleProcessorsPass,
    Behat\Behat\DependencyInjection\Compiler\DefinitionProposalsPass,
    Behat\Behat\DependencyInjection\Compiler\ContextClassGuessersPass,
    Behat\Behat\DependencyInjection\Compiler\ContextInitializersPass,
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
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class BehatApplication extends Application
{
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
    public function getDefinition()
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
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return integer 0 if everything went fine, or an error code
     */
    public function doRun(InputInterface $input, OutputInterface $output)
    {
        // construct container and load extensions
        $container = new ContainerBuilder();
        $this->loadConfiguration($container, $input);
        $this->loadExtensions($container);
        $this->resolveRelativePaths($container);

        // add core compiler passes
        $container->addCompilerPass(new ConsoleProcessorsPass());
        $container->addCompilerPass(new GherkinLoadersPass());
        $container->addCompilerPass(new ContextLoadersPass());
        $container->addCompilerPass(new ContextClassGuessersPass());
        $container->addCompilerPass(new ContextInitializersPass());
        $container->addCompilerPass(new DefinitionProposalsPass());
        $container->addCompilerPass(new FormattersPass());
        $container->addCompilerPass(new EventSubscribersPass());

        // compile and freeze container
        $container->compile();

        // setup command into application
        $this->add($container->get('behat.console.command'));

        return parent::doRun($input, $output);
    }

    /**
     * Configures container based on providen config file and profile.
     *
     * @param ContainerInterface $container
     * @param InputInterface     $input
     */
    protected function loadConfiguration(ContainerInterface $container, InputInterface $input)
    {
        $extension  = new BehatExtension();
        $cwd        = getcwd();
        $configFile = $input->getParameterOption(array('--config', '-c'));
        $profile    = $input->getParameterOption(array('--profile', '-p')) ?: 'default';
        $configs    = array();

        // if config file is not provided
        if (!$configFile) {
            // then use behat.yml
            if (is_file($cwd.DIRECTORY_SEPARATOR.'behat.yml')) {
                $configFile = $cwd.DIRECTORY_SEPARATOR.'behat.yml';
            // or behat.yml.dist
            } elseif (is_file($cwd.DIRECTORY_SEPARATOR.'behat.yml.dist')) {
                $configFile = $cwd.DIRECTORY_SEPARATOR.'behat.yml.dist';
            // or config/behat.yml
            } elseif (is_file($cwd.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'behat.yml')) {
                $configFile = $cwd.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'behat.yml';
            }
        }

        // read configuration
        $loader  = new Loader($configFile);
        $configs = $loader->loadConfiguration($profile);

        // load core extension into temp container
        $extension->load($configs, $container);
        $container->addObjectResource($extension);

        // locate base path
        $basePath = $cwd;
        if (file_exists($configFile)) {
            $basePath = dirname($configFile);
        }

        $container->setParameter('behat.paths.base', rtrim($basePath, DIRECTORY_SEPARATOR));
    }

    /**
     * Loads Behat extensions.
     *
     * @param ContainerBuilder $container
     *
     * @throws \InvalidArgumentException
     */
    protected function loadExtensions(ContainerBuilder $container)
    {
        $basePath = $container->getParameter('behat.paths.base');
        foreach ($container->getParameter('behat.extensions') as $id => $config) {
            $extension = null;
            if (class_exists($id)) {
                $extension = new $id;
            } elseif (file_exists($basePath.DIRECTORY_SEPARATOR.$id)) {
                $extension = require($basePath.DIRECTORY_SEPARATOR.$id);
            } else {
                $extension = require($id);
            }

            if (null === $extension) {
                throw new \InvalidArgumentException(sprintf(
                    '"%s" extension could not be found.', $id
                ));
            }
            if (!is_object($extension)) {
                throw new \InvalidArgumentException(sprintf(
                    '"%s" extension could not be initialized.', $id
                ));
            }
            if (!$extension instanceof ExtensionInterface) {
                throw new \InvalidArgumentException(sprintf(
                    '"%s" extension class should implement ExtensionInterface.',
                    get_class($extension)
                ));
            }

            // load extension into temp container
            $tempContainer = new ContainerBuilder();
            $tempContainer->setParameter('behat.paths.base',
                $container->getParameter('behat.paths.base')
            );

            $extension->load((array) $config, $tempContainer);
            $tempContainer->addObjectResource($extension);

            // merge temp container into main
            $container->merge($tempContainer);

            // add extension compiler passes
            foreach ($extension->getCompilerPasses() as $pass) {
                $container->addCompilerPass($pass);
            }
        }
    }

    /**
     * Resolves relative behat.paths.* parameters in container.
     *
     * @param ContainerBuilder $container
     */
    protected function resolveRelativePaths(ContainerBuilder $container)
    {
        $basePath      = $container->getParameter('behat.paths.base');
        $featuresPath  = $container->getParameter('behat.paths.features');
        $bootstrapPath = $container->getParameter('behat.paths.bootstrap');
        $parameterBag  = $container->getParameterBag();
        $featuresPath  = $parameterBag->resolveValue($featuresPath);
        $bootstrapPath = $parameterBag->resolveValue($bootstrapPath);

        if (!$this->isAbsolutePath($featuresPath)) {
            $featuresPath = $basePath.DIRECTORY_SEPARATOR.$featuresPath;
            $container->setParameter('behat.paths.features', $featuresPath);
        }
        if (!$this->isAbsolutePath($bootstrapPath)) {
            $bootstrapPath = $basePath.DIRECTORY_SEPARATOR.$bootstrapPath;
            $container->setParameter('behat.paths.bootstrap', $bootstrapPath);
        }
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
     * Returns whether the file path is an absolute path.
     *
     * @param string $file A file path
     *
     * @return Boolean
     */
    private function isAbsolutePath($file)
    {
        if ($file[0] == '/' || $file[0] == '\\'
            || (strlen($file) > 3 && ctype_alpha($file[0])
                && $file[1] == ':'
                && ($file[2] == '\\' || $file[2] == '/')
            )
            || null !== parse_url($file, PHP_URL_SCHEME)
        ) {
            return true;
        }

        return false;
    }
}
