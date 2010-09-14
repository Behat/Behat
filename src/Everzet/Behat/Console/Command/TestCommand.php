<?php

namespace Everzet\Behat\Console\Command;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Everzet\Behat\Exception\Redundant;

/*
 * This file is part of the Behat package.
 * (c) 2010 Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Behat application test command.
 *
 * @package     Behat
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class TestCommand extends Command
{
    protected $container;

    /**
     * @see Symfony\Component\Console\Command\Command
     */
    protected function configure()
    {
        $this->setName('test');

        // Load container
        $this->container                = new ContainerBuilder();
        $xmlLoader                      = new XmlFileLoader($this->container);
        $xmlLoader->load(               __DIR__ . '/../../ServiceContainer/container.xml');
        $this->container->setParameter( 'i18n.path', realpath(__DIR__ . '/../../../../../i18n'));

        // Load external config file (behat.(xml/yml))
        if (is_file(($cwd = getcwd()) . '/behat.xml')) {
            $xmlLoader->import($cwd . '/behat.xml');
        } elseif (is_file($cwd . '/behat.yml')) {
            $yamlLoader = new YamlFileLoader($this->container);
            $yamlLoader->import($cwd . '/behat.yml');
        }

        $this->setDefinition(array(
            new InputArgument('features',               InputArgument::OPTIONAL
              , 'Features path'
              , $this->container->getParameter('features.path')
            ),
            new InputOption('--format',         '-f',   InputOption::PARAMETER_REQUIRED
              , 'Change output formatter'
              , $this->container->getParameter('formatter.name')
            ),
            new InputOption('--tags',           '-t',   InputOption::PARAMETER_REQUIRED
              , 'Only executes features or scenarios with specified tags'
              , $this->container->getParameter('filter.tags')
            ),
        ));
    }

    /**
     * @see Symfony\Component\Console\Command\Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $featuresPath   = realpath($input->getArgument('features'));
        $featureFile    = null;

        // Find features path
        if (is_dir($featuresPath . '/features')) {
            $featuresPath = $featuresPath . '/features';
        } elseif (is_file($featuresPath)) {    
            $featureFile    = $featuresPath;
            $featuresPath   = dirname($featuresPath);
        }

        // Set container parameters
        $this->container->set('output',                     $output);
        $this->container->setParameter('features.path',     $featuresPath);
        $this->container->setParameter('features.file',     $featureFile);
        $this->container->setParameter('formatter.name',    $input->getOption('format'));
        $this->container->setParameter('filter.tags',       $input->getOption('tags'));
        $this->container->setParameter('formatter.verbose', $input->getOption('verbose'));

        // Fill embed parameter holders
        $this->container->setParameter('formatter.name', 
            ucfirst($this->container->getParameter('formatter.name'))
        );
        foreach ($this->container->getParameterBag()->all() as $key => $value) {
            $container = $this->container;
            $this->container->setParameter($key,
                preg_replace_callback('/%%([^%]+)%%/', function($matches) use($container) {
                    return $container->getParameter($matches[1]);
                }
              , $value
            ));
        }

        // Load & bind hooks
        $this->container->getHooksLoaderService();

        // Check if we had redundant definitions
        try {
            $this->container->getStepsLoaderService();
        } catch (Redundant $e) {
            $output->write(sprintf("\033[31m%s\033[0m",
                strtr($e->getMessage(), array($basePath . '/' => ''))
            ), true, 1);
            return 1;
        }

        // Get features loader, run test suite & return exit code
        return $this->container->
            getFeaturesLoaderService()->
            getFeaturesRunner()->
            run();
    }
}
