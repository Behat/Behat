<?php

namespace Everzet\Behat\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Everzet\Behat\ServiceContainer\ServiceContainer;
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
    /**
     * @see Symfony\Component\Console\Command\Command
     */
    protected function configure()
    {
        $this->setName('test');

        $this->setDefinition(array(
            new InputArgument('features', InputArgument::OPTIONAL, 'Features path', 'features'),
            new InputOption('--format', '-f', InputOption::PARAMETER_REQUIRED, 'Change output formatter', 'pretty')
        ));
    }

    /**
     * @see Symfony\Component\Console\Command\Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $basePath = realpath($input->getArgument('features'));

        // Find features path
        if (is_dir($basePath . '/features')) {
            $basePath .= '/features';
        } elseif (is_file($basePath)) {
            $basePath = dirname($basePath);
        }

        // Configure DIC
        $container = new ServiceContainer();
        $container->setParameter('i18n.path',           realpath(__DIR__ . '/../../../../../i18n'));
        $container->setParameter('features.path',       $basePath);
        $container->setParameter('steps.path',          $basePath . '/steps');
        $container->setParameter('environment.file',    $basePath . '/support/env.php');
        $container->setParameter('formatter.output',    $output);
        $container->setParameter('formatter.verbose',   $input->getOption('verbose'));
        $container->setParameter('formatter.name',      ucfirst($input->getOption('format')));

        // Check if we had redundant definitions
        try {
            $container->getStepsLoaderService();
        } catch (Redundant $e) {
            $output->writeln(sprintf("<failed>%s</failed>",
                strtr($e->getMessage(), array($basePath . '/' => ''))
            ));
            return 1;
        }

        // Get features loader
        $loader = $container->getFeaturesLoaderService();

        // Run test suite
        $loader->getFeaturesRunner()->run();
    }
}
