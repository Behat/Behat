<?php

namespace Behat\Behat\Console\Processor;

use Symfony\Component\DependencyInjection\ContainerInterface,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Input\InputOption,
    Symfony\Component\Console\Output\OutputInterface;

use Behat\Behat\DependencyInjection\BehatExtension;

class ContainerProcessor implements ProcessorInterface
{
    /**
     * @see     Behat\Behat\Console\Configuration\ProcessorInterface::getInputOptions()
     */
    public function getInputOptions()
    {
        return array(
            new InputOption('--config',         '-c',
                InputOption::VALUE_REQUIRED,
                '  ' .
                'Specify external configuration file to load. ' .
                '<comment>behat.yml</comment> or <comment>config/behat.yml</comment> will be used by default.'
            ),
            new InputOption('--profile',        '-p',
                InputOption::VALUE_REQUIRED,
                ' ' .
                'Specify configuration profile to use. ' .
                'Define profiles in config file (<info>--config</info>).'."\n"
            ),
        );
    }

    /**
     * @see     Behat\Behat\Console\Configuration\ProcessorInterface::process()
     */
    public function process(ContainerInterface $container, InputInterface $input, OutputInterface $output)
    {
        $extension  = new BehatExtension();
        $cwd        = getcwd();
        $configFile = $input->hasOption('config')  ? $input->getOption('config')  : null;
        $profile    = $input->hasOption('profile') ? $input->getOption('profile') : null;

        if (null === $profile) {
            $profile = 'default';
        }

        if (null === $configFile) {
            if (is_file($cwd.DIRECTORY_SEPARATOR.'behat.yml')) {
                $configFile = $cwd.DIRECTORY_SEPARATOR.'behat.yml';
            } elseif (is_file($cwd.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'behat.yml')) {
                $configFile = $cwd.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'behat.yml';
            }
        }

        if (null !== $configFile) {
            $config = $extension->loadFromFile($configFile, $profile, $container);
        } else {
            $config = $extension->load(array(array()), $container);
        }
        $container->compile();

        if (null !== $configFile) {
            $container->get('behat.path_locator')->setPathConstant('BEHAT_CONFIG_PATH', dirname($configFile));
        }
    }
}
