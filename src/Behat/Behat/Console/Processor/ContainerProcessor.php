<?php

namespace Behat\Behat\Console\Processor;

use Symfony\Component\DependencyInjection\ContainerInterface,
    Symfony\Component\Console\Command\Command,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Input\InputOption,
    Symfony\Component\Console\Output\OutputInterface;

use Behat\Behat\DependencyInjection\BehatExtension;

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
                'Specify external configuration file to load. ' .
                '<comment>behat.yml</comment> or <comment>config/behat.yml</comment> will be used by default.'
            )
            ->addOption('--profile', '-p', InputOption::VALUE_REQUIRED,
                'Specify configuration profile to use. ' .
                'Define profiles in config file (<info>--config</info>).'
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
        $configFile = $input->hasOption('config')  ? $input->getOption('config')  : null;
        $profile    = $input->hasOption('profile') ? $input->getOption('profile') : 'default';

        if (null === $configFile) {
            if (is_file($cwd.DIRECTORY_SEPARATOR.'behat.yml')) {
                $configFile = $cwd.DIRECTORY_SEPARATOR.'behat.yml';
            } elseif (is_file($cwd.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'behat.yml')) {
                $configFile = $cwd.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'behat.yml';
            }
        }

        if (file_exists($configFile)) {
            $config = $extension->loadFromFile($configFile, $profile, $container);
        } else {
            $config = $extension->load(array(array()), $container);
        }
        $container->compile();

        if (file_exists($configFile)) {
            $container->get('behat.path_locator')->setPathConstant('BEHAT_CONFIG_PATH', dirname($configFile));
        }
    }
}
