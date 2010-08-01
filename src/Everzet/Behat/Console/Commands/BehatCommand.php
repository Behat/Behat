<?php

namespace Everzet\Behat\Console\Commands;

use \Symfony\Components\Console\Command\Command;
use \Symfony\Components\Console\Input\InputInterface;
use \Symfony\Components\Console\Input\InputArgument;
use \Symfony\Components\Console\Input\InputOption;
use \Symfony\Components\Console\Output\OutputInterface;

use \Everzet\Behat\Stats\TestStats;
use \Everzet\Behat\Exceptions\Redundant;

/*
 * This file is part of the behat package.
 * (c) 2010 Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Behat console test command.
 *
 * @package     behat
 * @subpackage  Behat
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class BehatCommand extends Command
{
    /**
     * @see \Symfony\Components\Console\Command\Command
     */
    protected function configure()
    {
        $this->setName('behat');

        $this->setDefinition(array(
            new InputArgument('features', InputArgument::OPTIONAL, 'Features folder', 'features')
        ));
    }

    /**
     * @see \Symfony\Components\Console\Command\Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = new \Symfony\Components\DependencyInjection\Builder();

        $container->
            register('parser', '%parser.class%')->
            addArgument(new \Symfony\Components\DependencyInjection\Reference('i18n'))->
            setShared(false);

        $container->
            register('i18n', '%i18n.class%')->
            addArgument('%i18n.path%')->
            setShared(false);

        $container->
            register('world', '%world.class%')->
            addArgument('%world.file%')->
            setShared(false);

        $container->
            register('features.loader', '%features.loader.class%')->
            addArgument('%features.path%')->
            addArgument('%container%')->
            setShared(false);

        $container->
            register('steps.loader', '%steps.loader.class%')->
            addArgument('%steps.loader.path%')->
            addArgument(new \Symfony\Components\DependencyInjection\Reference('world'))->
            setShared(false);

        $container->
            register('printer', '%printer.class%')->
            addArgument('%output%')->
            addArgument('%features.path%')->
            addArgument('%printer.verbose%')->
            setShared(false);

        // Default parameters
        $container->setParameter('parser.class', 'Everzet\\Gherkin\\Parser');
        $container->setParameter('i18n.class', 'Everzet\\Gherkin\\I18n');
        $container->setParameter('world.class', 'Everzet\\Behat\\Environment\\SimpleWorld');
        $container->setParameter('features.loader.class', 'Everzet\\Behat\\Loaders\\FeaturesLoader');
        $container->setParameter('steps.loader.class', 'Everzet\\Behat\\Loaders\\StepsLoader');
        $container->setParameter('printer.class', 'Everzet\\Behat\\Printers\\ConsolePrinter');

        $dumper = new \Symfony\Components\DependencyInjection\Dumper\PhpDumper($container);
        file_put_contents(__DIR__ . '/../../ServiceContainer/Container.php', $dumper->dump(
            array('class' => 'Container')
        ));




        $basePath = realpath($input->getArgument('features'));
        $featureFiles = array();

        if (is_dir($basePath.'/features')) {
            $basePath .= '/features';
        } elseif (is_file($basePath)) {
            $featureFiles[] = $basePath;
            $basePath = dirname($basePath);
        }

        // Sets parameters
        $container->setParameter('container',           $container);
        $container->setParameter('output',              $output);
        $container->setParameter('i18n.path',           realpath(__DIR__ . '/../../../../../i18n'));
        $container->setParameter('features.path',       $basePath);
        $container->setParameter('world.file',          $basePath . '/support/env.php');
        $container->setParameter('steps.loader.path',   $basePath . '/steps');
        $container->setParameter('printer.verbose',     $input->getOption('verbose'));

        // Check if we had redundant definitions
        try {
            $container->getSteps_LoaderService();
        } catch (Redundant $e) {
            $output->writeln(sprintf("<failed>%s</failed>",
                strtr($e->getMessage(), array($basePath . '/' => ''))
            ));
            return 1;
        }

        foreach ($container->getFeatures_LoaderService() as $feature) {
            $feature->run();
        }
    }
}
