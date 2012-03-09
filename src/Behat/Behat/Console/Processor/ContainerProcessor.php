<?php

namespace Behat\Behat\Console\Processor;

use Symfony\Component\DependencyInjection\ContainerInterface,
    Symfony\Component\Console\Command\Command,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Input\InputOption,
    Symfony\Component\Console\Output\OutputInterface;

use Behat\Behat\DependencyInjection\BehatExtension,
    Behat\Behat\DependencyInjection\Compiler\GherkinPass,
    Behat\Behat\DependencyInjection\Compiler\ContextReaderPass,
    Behat\Behat\DependencyInjection\Compiler\EventDispatcherPass;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Service container processor.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class ContainerProcessor implements ProcessorInterface
{
    /**
     * @see     Behat\Behat\Console\Configuration\ProcessorInterface::configure()
     */
    public function configure(Command $command)
    {
        $command
            ->addOption('--config', '-c', InputOption::VALUE_REQUIRED,
                "Specify external configuration file to load.\n" .
                "<comment>behat.yml</comment> or <comment>config/behat.yml</comment> will be used by default."
            )
            ->addOption('--profile', '-p', InputOption::VALUE_REQUIRED,
                "Specify configuration profile to use.\n" .
                "Define profiles in config file (<info>--config</info>)."
            )
        ;
    }

    /**
     * @see     Behat\Behat\Console\Configuration\ProcessorInterface::process()
     */
    public function process(ContainerInterface $container, InputInterface $input, OutputInterface $output)
    {
        $extension  = new BehatExtension();
        $cwd        = getcwd();
        $configFile = $input->getOption('config');
        $profile    = $input->getOption('profile') ?: 'default';
        $configs    = array();

        if (null === $configFile) {
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

        $container->addCompilerPass(new GherkinPass());
        $container->addCompilerPass(new ContextReaderPass());
        $container->addCompilerPass(new EventDispatcherPass());
        $container->compile();

        if (file_exists($configFile)) {
            $container->get('behat.path_locator')->setPathConstant(
                'BEHAT_CONFIG_PATH', dirname($configFile)
            );
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
